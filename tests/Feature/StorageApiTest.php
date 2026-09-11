<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StorageApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_authenticated_user_can_upload_and_issue_public_and_private_urls(): void
    {
        Storage::fake('local');

        $token = $this->postJson('/api/auth/register', [
            'name' => 'Storage Owner',
            'email' => 'owner@example.test',
            'password' => 'a-strong-password',
            'password_confirmation' => 'a-strong-password',
        ])->assertCreated()->json('token');

        $bucket = $this->withToken($token)
            ->postJson('/api/buckets', ['name' => 'media'])
            ->assertCreated()
            ->json('data.id');

        $upload = $this->withToken($token)->post('/api/buckets/'.$bucket.'/objects', [
            'file' => UploadedFile::fake()->create('hello.txt', 1, 'text/plain'),
            'visibility' => 'public',
        ]);

        $upload->assertCreated()->assertJsonPath('data.visibility', 'public');
        $objectId = $upload->json('data.id');
        $publicUrl = $upload->json('data.public_url');

        $this->get($publicUrl)->assertOk();

        $privateUrl = $this->withToken($token)
            ->postJson('/api/objects/'.$objectId.'/private-url', ['expires_in' => 5])
            ->assertOk()
            ->json('url');

        $this->get($privateUrl)->assertOk();
    }

    public function test_private_objects_are_not_publicly_readable(): void
    {
        Storage::fake('local');

        $user = User::factory()->create();
        $bucket = $user->buckets()->create(['name' => 'private-files']);
        $object = $bucket->objects()->create([
            'user_id' => $user->id,
            'key' => 'buckets/'.$bucket->id.'/secret.txt',
            'original_name' => 'secret.txt',
            'mime_type' => 'text/plain',
            'size' => 6,
            'checksum' => hash('sha256', 'secret'),
            'disk' => 'local',
            'visibility' => 'private',
        ]);
        Storage::disk('local')->put($object->key, 'secret');

        $this->get('/api/objects/'.$object->id.'/public')->assertNotFound();
    }

    public function test_scoped_access_tokens_limit_operations(): void
    {
        Storage::fake('local');

        $fullToken = $this->postJson('/api/auth/register', [
            'name' => 'Token Owner',
            'email' => 'token-owner@example.test',
            'password' => 'a-strong-password',
            'password_confirmation' => 'a-strong-password',
        ])->assertCreated()->json('token');

        $bucket = $this->withToken($fullToken)
            ->postJson('/api/buckets', ['name' => 'scoped'])
            ->assertCreated()
            ->json('data.id');

        $scopedToken = $this->withToken($fullToken)
            ->postJson('/api/access-tokens', [
                'name' => 'Uploader only',
                'abilities' => ['objects:write'],
                'expires_in' => 3600,
            ])
            ->assertCreated()
            ->json('token');

        $this->app['auth']->forgetGuards();

        $this->withToken($scopedToken)
            ->getJson('/api/buckets')
            ->assertForbidden();

        $this->app['auth']->forgetGuards();

        $this->withToken($scopedToken)
            ->post('/api/buckets/'.$bucket.'/objects', [
                'file' => UploadedFile::fake()->create('scoped.txt', 1, 'text/plain'),
            ])
            ->assertCreated();
    }
}