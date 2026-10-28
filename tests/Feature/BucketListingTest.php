<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BucketListingTest extends TestCase
{
    use RefreshDatabase;

    private function bucketWithFiles(int $count = 27)
    {
        $user = User::factory()->create();
        $this->withToken($user->createToken('listing', ['buckets:read'])->plainTextToken);
        $bucket = $user->buckets()->create(['name' => 'reports']);
        foreach (range(1, $count) as $index) {
            $bucket->objects()->create([
                'user_id' => $user->id, 'key' => 'reports/'.$index,
                'original_name' => sprintf('report-%03d.docx', $index),
                'mime_type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'size' => $index * 100, 'checksum' => hash('sha256', (string) $index),
                'disk' => 'local', 'visibility' => $index % 2 ? 'private' : 'public',
            ]);
        }
        return $bucket;
    }

    public function test_listing_pages_all_files_and_reports_bucket_totals(): void
    {
        $bucket = $this->bucketWithFiles();
        $this->getJson('/api/buckets/'.$bucket->id.'?sort=name')->assertOk()
            ->assertJsonCount(25, 'data.objects')->assertJsonPath('data.objects.0.name', 'report-001.docx')
            ->assertJsonPath('data.pagination.total', 27)->assertJsonPath('data.pagination.last_page', 2)
            ->assertJsonPath('data.objects_count', 27)->assertJsonPath('data.private_count', 14)
            ->assertJsonPath('data.bytes', 37800);
        $this->getJson('/api/buckets/'.$bucket->id.'?sort=name&page=2')->assertOk()
            ->assertJsonCount(2, 'data.objects')->assertJsonPath('data.objects.0.name', 'report-026.docx')
            ->assertJsonPath('data.pagination.from', 26)->assertJsonPath('data.pagination.to', 27);
    }

    public function test_search_visibility_and_size_sort_apply_to_the_entire_bucket(): void
    {
        $bucket = $this->bucketWithFiles();
        $this->getJson('/api/buckets/'.$bucket->id.'?search=report-02&visibility=private&sort=largest')->assertOk()
            ->assertJsonCount(4, 'data.objects')->assertJsonPath('data.objects.0.name', 'report-027.docx')
            ->assertJsonPath('data.pagination.total', 4)->assertJsonPath('data.objects_count', 27);
        $this->getJson('/api/buckets/'.$bucket->id.'?search=does-not-exist')->assertOk()
            ->assertJsonCount(0, 'data.objects')->assertJsonPath('data.pagination.total', 0);
    }

    public function test_filters_do_not_bypass_ownership_and_invalid_sorts_are_rejected(): void
    {
        $bucket = $this->bucketWithFiles(1);
        $this->getJson('/api/buckets/'.$bucket->id.'?sort=unsafe')->assertUnprocessable();
        $this->getJson('/api/buckets/'.$bucket->id.'?page=0')->assertUnprocessable();
        $other = User::factory()->create()->buckets()->create(['name' => 'another-owner']);
        $this->getJson('/api/buckets/'.$other->id.'?search=report')->assertNotFound();
    }
}
