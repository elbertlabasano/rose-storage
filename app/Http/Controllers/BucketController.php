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

        return response()->json([
            'data' => $bucket->loadCount('objects')->load([
                'objects' => fn ($query) => $query->latest()->limit(50),
            ]),
        ]);
    }

    private function assertOwner(Request $request, Bucket $bucket): void
    {
        abort_unless($bucket->user_id === $request->user()->id, 404);
    }
}


