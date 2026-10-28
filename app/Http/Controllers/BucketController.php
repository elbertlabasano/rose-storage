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

        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:200'],
            'visibility' => ['sometimes', Rule::in(['all', 'private', 'public'])],
            'sort' => ['sometimes', Rule::in(['newest', 'oldest', 'name', 'largest'])],
            'page' => ['sometimes', 'integer', 'min:1'],
        ]);
        $query = $bucket->objects();
        if (filled($filters['search'] ?? null)) {
            $query->where('original_name', 'like', '%'.$filters['search'].'%');
        }
        if (($filters['visibility'] ?? 'all') !== 'all') {
            $query->where('visibility', $filters['visibility']);
        }
        [$column, $direction] = match ($filters['sort'] ?? 'newest') {
            'oldest' => ['created_at', 'asc'], 'name' => ['original_name', 'asc'],
            'largest' => ['size', 'desc'], default => ['created_at', 'desc'],
        };
        $page = $query->orderBy($column, $direction)->orderBy('id')
            ->paginate(25, ['id', 'bucket_id', 'original_name', 'mime_type', 'size', 'checksum', 'visibility', 'created_at']);

        $objects = $page->getCollection()
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
                'bytes' => (int) $bucket->objects()->sum('size'),
                'private_count' => $bucket->objects()->where('visibility', 'private')->count(),
                'pagination' => [
                    'current_page' => $page->currentPage(), 'last_page' => $page->lastPage(),
                    'total' => $page->total(), 'from' => $page->firstItem(), 'to' => $page->lastItem(),
                ],
                'objects' => $objects,
            ],
        ]);
    }

    private function assertOwner(Request $request, Bucket $bucket): void
    {
        abort_unless($bucket->user_id === $request->user()->id, 404);
    }
}
