<?php

namespace App\Http\Controllers;

use App\Models\Bucket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class BucketController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'data' => $request->user()->buckets()->withCount('objects')->latest()->paginate(50),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => [
                'required', 'string', 'min:3', 'max:63',
                'regex:/^[a-z0-9][a-z0-9.-]*[a-z0-9]$/',
                Rule::unique('buckets', 'name')->where('user_id', $request->user()->id),
            ],
        ]);

        $bucket = $request->user()->buckets()->create([
            'name' => Str::lower($data['name']),
        ]);

        return response()->json(['data' => $bucket], 201);
    }

    public function show(Request $request, Bucket $bucket): JsonResponse
    {
        $this->assertOwner($request, $bucket);

        $objects = $bucket->objects()
            ->latest()
            ->limit(50)
            ->get(['id', 'bucket_id', 'original_name', 'mime_type', 'size', 'checksum', 'visibility', 'created_at'])
            ->map(fn ($object) => [
                'id' => $object->id,
                'bucket_id' => $object->bucket_id,
                'name' => $object->original_name,
                'mime_type' => $object->mime_type,
                'size' => $object->size,
                'checksum' => $object->checksum,
                'visibility' => $object->visibility,
                'created_at' => $object->created_at,
                'public_url' => $object->visibility === 'public'
                    ? route('objects.public-download', ['object' => $object->id])
                    : null,
            ]);

        return response()->json([
            'data' => [
                'id' => $bucket->id,
                'name' => $bucket->name,
                'objects_count' => $bucket->objects()->count(),
                'objects' => $objects,
            ],
        ]);
    }

    private function assertOwner(Request $request, Bucket $bucket): void
    {
        abort_unless($bucket->user_id === $request->user()->id, 404);
    }
}