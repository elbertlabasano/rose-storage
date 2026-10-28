<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\IncrementalBackupService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use ZipArchive;

class IncrementalBackupTest extends TestCase
{
    use RefreshDatabase;

    private const PASSWORD = 'example-test-password';

    private function object()
    {
        config(['backups.enabled' => true]);
        Storage::fake('local');
        Storage::fake('backup');
        Http::preventStrayRequests();
        $user = User::factory()->create();
        $bucket = $user->buckets()->create(['name' => 'media']);
        $key = 'buckets/'.$bucket->id.'/11111111-1111-4111-8111-111111111111.txt';
        Storage::disk('local')->put($key, 'hello');
        return $bucket->objects()->create([
            'user_id' => $user->id, 'key' => $key, 'original_name' => 'hello.txt',
            'mime_type' => 'text/plain', 'size' => 5, 'checksum' => hash('sha256', 'hello'),
            'disk' => 'local', 'visibility' => 'private',
        ]);
    }

    private function runBackup(int $exit = 0, ?string $password = self::PASSWORD): void
    {
        config(['backups.password' => $password]);
        $this->artisan('storage:backup')
            ->assertExitCode($exit);
    }

    public function test_every_entry_requires_the_password_and_correct_password_restores_bytes(): void
    {
        $object = $this->object();
        $this->runBackup();
        $key = json_decode(DB::table('object_backups')->value('manifest'), true)['archive'];
        $zip = new ZipArchive;
        $this->assertTrue($zip->open(Storage::disk('backup')->path($key)));
        for ($index = 0; $index < $zip->numFiles; $index++) {
            $this->assertSame(ZipArchive::EM_AES_256, $zip->statIndex($index)['encryption_method']);
            $this->assertFalse($zip->getFromIndex($index));
        }
        $zip->setPassword('incorrect-password');
        $this->assertFalse($zip->getFromName('files/'.$object->key));
        $zip->setPassword(self::PASSWORD);
        $this->assertSame('hello', $zip->getFromName('files/'.$object->key));
        $manifest = json_decode($zip->getFromName('manifest.json'), true);
        $this->assertSame($object->checksum, $manifest['objects'][0]['object']['checksum']);
        $zip->close();
        Http::assertNothingSent();
    }

    public function test_completed_files_are_skipped_and_local_archive_is_retained_after_deletion(): void
    {
        $object = $this->object();
        $this->runBackup();
        Storage::disk('local')->delete($object->key);
        $this->runBackup();
        $object->delete();
        $this->assertCount(1, Storage::disk('backup')->allFiles());
        $this->assertDatabaseCount('object_backups', 1);
    }

    public function test_corrupt_source_aborts_the_zip_and_can_be_retried(): void
    {
        $object = $this->object();
        Storage::disk('local')->put($object->key, 'wrong');
        $this->runBackup(1);
        $this->assertDatabaseCount('object_backups', 0);
        $this->assertSame([], Storage::disk('backup')->allFiles());
        $this->assertSame([], glob(storage_path('framework/backups/*-*-*-*-*')));
        Storage::disk('local')->put($object->key, 'hello');
        $this->runBackup();
        $this->assertDatabaseCount('object_backups', 1);
    }

    public function test_missing_and_short_configured_passwords_do_not_create_archives(): void
    {
        $this->object();
        $this->runBackup(1, 'short');
        $this->runBackup(1, null);
        $this->assertSame([], Storage::disk('backup')->allFiles());
        $this->assertDatabaseCount('object_backups', 0);
    }

    public function test_disabled_and_overlapping_runs_do_not_prompt_or_create_archives(): void
    {
        $this->object();
        config(['backups.enabled' => false]);
        $this->artisan('storage:backup')->assertExitCode(1);
        config(['backups.enabled' => true]);
        $lock = Cache::lock('storage:incremental-backup', 0);
        $this->assertTrue($lock->get());
        try {
            $this->artisan('storage:backup')->assertExitCode(1);
        } finally {
            $lock->release();
        }
        $this->runBackup();
    }

    public function test_all_pending_files_are_in_one_encrypted_zip_with_metadata_changes_in_later_zip(): void
    {
        $first = $this->object();
        $second = $first->replicate();
        $second->key = 'buckets/'.$first->bucket_id.'/22222222-2222-4222-8222-222222222222.txt';
        $second->save();
        Storage::disk('local')->put($second->key, 'hello');
        Storage::disk('local')->put('unregistered.txt', 'do not export');
        $this->runBackup();
        $files = Storage::disk('backup')->allFiles();
        $this->assertCount(1, $files);
        $zip = new ZipArchive;
        $zip->open(Storage::disk('backup')->path($files[0]));
        $zip->setPassword(self::PASSWORD);
        $this->assertSame('hello', $zip->getFromName('files/'.$first->key));
        $this->assertSame('hello', $zip->getFromName('files/'.$second->key));
        $this->assertFalse($zip->locateName('unregistered.txt'));
        $this->assertSame(2, json_decode($zip->getFromName('manifest.json'), true)['file_count']);
        $zip->close();
        $this->travel(2)->seconds();
        $first->update(['visibility' => 'public']);
        $this->assertSame(1, app(IncrementalBackupService::class)->pending()->count());
        $this->runBackup();
        $this->assertCount(2, Storage::disk('backup')->allFiles());
    }
}
