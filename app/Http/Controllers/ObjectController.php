<?php

namespace App\Http\Controllers;

use App\Models\Bucket;
use App\Models\StoredObject;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\File;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class ObjectController extends Controller
{
    public function store(Request $request, Bucket $bucket): JsonResponse
    {
        abort_unless($bucket->user_id === $request->user()->id, 404);

        $data = $request->validate([
            'file' => [
                'required', 'file',
                File::types(config('storage.allowed_mimes'))
                    ->max(config('storage.max_upload_mb').'mb'),
            ],
            'visibility' => ['sometimes', 'string', 'in:private,public'],
        ]);

        $file = $data['file'];
        $extension = strtolower($file->guessExtension() ?: $file->extension() ?: 'bin');
        $key = 'buckets/'.$bucket->id.'/'.Str::uuid().'.'.$extension;
        $diskName = config('filesystems.default');
        $disk = Storage::disk($diskName);

        try {
            $written = $disk->putFileAs('', $file, $key, ['visibility' => 'private']);

            if (! $written) {
                throw new \RuntimeException('The storage disk rejected the upload.');
            }

            $object = $bucket->objects()->create([
                'user_id' => $request->user()->id,
                'key' => $key,
                'original_name' => preg_replace('/[\x00-\x1F\x7F]/u', '', $file->getClientOriginalName()) ?: 'file',
                'mime_type' => $file->getMimeType() ?: 'application/octet-stream',
                'size' => $file->getSize(),
                'checksum' => hash_file('sha256', $file->getRealPath()),
                'disk' => $diskName,
                'visibility' => $data['visibility'] ?? 'private',
                'metadata' => [
                    'uploaded_at' => now()->toISOString(),
                ],
            ]);
        } catch (Throwable $exception) {
            if (isset($object) === false && isset($key)) {
                $disk->delete($key);
            }

            throw $exception;
        }

        return response()->json(['data' => $this->representation($object)], 201);
    }

    public function show(Request $request, StoredObject $object): JsonResponse
    {
        $this->assertOwner($request, $object);

        return response()->json(['data' => $this->representation($object)]);
    }

    public function destroy(Request $request, StoredObject $object): JsonResponse
    {
        $this->assertOwner($request, $object);

        Storage::disk($object->disk)->delete($object->key);
        $object->delete();

        return response()->json(['message' => 'Object deleted.']);
    }

    public function visibility(Request $request, StoredObject $object): JsonResponse
    {
        $this->assertOwner($request, $object);

        $data = $request->validate([
            'visibility' => ['required', 'string', 'in:private,public'],
        ]);

        $object->update(['visibility' => $data['visibility']]);

        return response()->json(['data' => $this->representation($object->fresh())]);
    }

    public function privateUrl(Request $request, StoredObject $object): JsonResponse
    {
        $this->assertOwner($request, $object);

        $max = config('storage.private_url_max_seconds');
        $seconds = (int) $request->input('expires_in', 3600);
        validator(['expires_in' => $seconds], [
            'expires_in' => ['integer', 'min:1', 'max:'.$max],
        ])->validate();

        $expiresAt = now()->addSeconds($seconds);
        $url = URL::temporarySignedRoute(
            'objects.private-download',
            $expiresAt,
            ['object' => $object->getRouteKey()]
        );

        return response()->json([
            'url' => $url,
            'expires_at' => $expiresAt->toISOString(),
        ]);
    }

    public function publicDownload(StoredObject $object): StreamedResponse
    {
        abort_unless($object->visibility === 'public', 404);

        return $this->stream($object);
    }

    public function privateDownload(StoredObject $object): StreamedResponse
    {
        return $this->stream($object);
    }

    private function stream(StoredObject $object): StreamedResponse
    {
        $stream = Storage::disk($object->disk)->readStream($object->key);
        abort_if($stream === false, 404);

        $safeName = str_replace(["\r", "\n", '"'], ['', '', ''], $object->original_name);

        return response()->stream(function () use ($stream): void {
            fpassthru($stream);
            fclose($stream);
        }, 200, [
            'Content-Type' => $object->mime_type,
            'Content-Length' => (string) $object->size,
            'Content-Disposition' => 'attachment; filename="'.$safeName.'"',
            'Cache-Control' => $object->visibility === 'public'
                ? 'public, max-age=3600'
                : 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    private function representation(StoredObject $object): array
    {
        return [
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
        ];
    }

    private function assertOwner(Request $request, StoredObject $object): void
    {
        abort_unless($object->user_id === $request->user()->id, 404);
    }
}







