<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\MysqlBackupService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BackupPageTest extends TestCase
{
    use RefreshDatabase;

    private function administrator(array $abilities = ['*']): User
    {
        config(['backups.enabled' => true, 'backups.password' => 'backup-test-password']);
        Storage::fake('local');
        Storage::fake('backup');
        Http::preventStrayRequests();
        $user = User::factory()->create();
        config(['backups.admin_user_ids' => [$user->id]]);
        $this->withToken($user->createToken('test', $abilities)->plainTextToken);
        return $user;
    }

    private function upload(User $user): void
    {
        $bucket = $user->buckets()->create(['name' => 'media']);
        $key = 'buckets/'.$bucket->id.'/11111111-1111-4111-8111-111111111111.txt';
        Storage::disk('local')->put($key, 'hello');
        $bucket->objects()->create([
            'user_id' => $user->id, 'key' => $key, 'original_name' => 'hello.txt',
            'mime_type' => 'text/plain', 'size' => 5, 'checksum' => hash('sha256', 'hello'), 'disk' => 'local', 'visibility' => 'private',
        ]);
    }

    public function test_only_configured_administrators_with_full_access_can_export(): void
    {
        $this->getJson('/api/backups')->assertUnauthorized();
        $this->administrator(['objects:read']);
        $this->getJson('/api/backups')->assertForbidden();
        $this->postJson('/api/backups/prepare/zip')->assertForbidden();
        $other = User::factory()->create();
        $this->withToken($other->createToken('test', ['*'])->plainTextToken);
        $this->postJson('/api/backups/prepare/sql')->assertForbidden();
        $this->assertDatabaseCount('backup_exports', 0);
    }

    public function test_overview_does_not_expose_password_or_server_paths(): void
    {
        $this->administrator();
        config(['backups.google_drive_folder_id' => 'folder_1234567890']);
        $response = $this->getJson('/api/backups')->assertOk()->assertJsonPath('pending', 0)
            ->assertJsonPath('drive_url', 'https://drive.google.com/drive/folders/folder_1234567890');
        $this->assertStringNotContainsString('backup-test-password', $response->getContent());
        $this->assertStringNotContainsString(storage_path(), $response->getContent());
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
    }

    public function test_zip_can_be_prepared_downloaded_and_downloaded_again_without_reexporting(): void
    {
        $user = $this->administrator();
        $this->upload($user);
        $response = $this->postJson('/api/backups/prepare/zip')->assertOk();
        $url = $response->json('url');
        $this->assertStringEndsWith('.zip', $response->json('filename'));
        $this->assertGreaterThan(0, $response->json('bytes'));
        $this->assertDatabaseCount('backup_exports', 1);
        $this->assertDatabaseCount('object_backups', 1);
        $this->get($url)->assertOk()->assertHeader('Content-Type', 'application/zip');
        $ajax = $this->withHeader('X-Requested-With', 'XMLHttpRequest')->get($url)
            ->assertOk()->assertHeader('Content-Type', 'application/octet-stream');
        $this->assertFalse($ajax->headers->has('Content-Disposition'));
        $this->withoutHeader('X-Requested-With');
        $id = DB::table('backup_exports')->value('id');
        $this->postJson('/api/backups/'.$id.'/download')->assertOk();
        $this->assertDatabaseCount('backup_exports', 1);
        $this->getJson('/api/backups')->assertJsonPath('pending', 0)->assertJsonPath('exports.0.administrator', $user->name);
        Http::assertNothingSent();
    }

    public function test_unsigned_and_expired_downloads_are_blocked(): void
    {
        $user = $this->administrator();
        $this->upload($user);
        $url = $this->postJson('/api/backups/prepare/zip')->assertOk()->json('url');
        $id = DB::table('backup_exports')->value('id');
        $this->get('/backup-downloads/'.$id)->assertForbidden();
        $this->travel(11)->minutes();
        $this->get($url)->assertForbidden();
    }

    public function test_preparation_requires_post_and_reports_an_existing_backup_lock(): void
    {
        $this->administrator();
        $this->getJson('/api/backups/prepare/zip')->assertStatus(405);
        $lock = Cache::lock('storage:incremental-backup', 0);
        $this->assertTrue($lock->get());
        try { $this->postJson('/api/backups/prepare/zip')->assertStatus(409); }
        finally { $lock->release(); }
    }

    public function test_no_pending_files_does_not_create_an_empty_export(): void
    {
        $this->administrator();
        $this->postJson('/api/backups/prepare/zip')->assertStatus(409);
        $this->assertDatabaseCount('backup_exports', 0);
    }

    public function test_sql_export_is_retained_privately_and_temporary_file_is_removed(): void
    {
        $this->administrator();
        $path = Storage::disk('backup')->path('temporary.sql');
        file_put_contents($path, '-- test SQL backup');
        $mock = \Mockery::mock(MysqlBackupService::class);
        $mock->shouldReceive('create')->once()->andReturn([
            'path' => $path, 'name' => 'Rose-Storage-database-test.sql', 'bytes' => filesize($path),
        ]);
        $this->app->instance(MysqlBackupService::class, $mock);
        $response = $this->postJson('/api/backups/prepare/sql')->assertOk();
        $this->assertFileDoesNotExist($path);
        $this->get($response->json('url'))->assertOk()->assertHeader('Content-Type', 'application/sql');
        $this->assertDatabaseHas('backup_exports', ['type' => 'sql']);
    }

    public function test_errors_do_not_leak_private_details_or_complete_exports(): void
    {
        $this->administrator();
        $mock = \Mockery::mock(MysqlBackupService::class);
        $mock->shouldReceive('create')->andThrow(new \RuntimeException('SECRET database password'));
        $this->app->instance(MysqlBackupService::class, $mock);
        $response = $this->postJson('/api/backups/prepare/sql')->assertStatus(503);
        $this->assertStringNotContainsString('SECRET', $response->getContent());
        $this->assertDatabaseCount('backup_exports', 0);
    }

    public function test_preparation_is_rate_limited(): void
    {
        $this->administrator();
        $this->postJson('/api/backups/prepare/zip')->assertStatus(409);
        $this->postJson('/api/backups/prepare/zip')->assertStatus(409);
        $this->postJson('/api/backups/prepare/zip')->assertStatus(429);
    }

    public function test_removing_administrator_access_invalidates_existing_download_links(): void
    {
        $user = $this->administrator();
        $this->upload($user);
        $url = $this->postJson('/api/backups/prepare/zip')->assertOk()->json('url');
        config(['backups.admin_user_ids' => []]);
        $this->get($url)->assertForbidden();
    }
}
