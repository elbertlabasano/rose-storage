<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Laravel\Sanctum\PersonalAccessToken;

class AccessTokenController extends Controller
{
    private const ABILITIES = [
        'buckets:read',
        'buckets:write',
        'objects:read',
        'objects:write',
        'objects:delete',
        'tokens:manage',
    ];

    public function index(Request $request): JsonResponse
    {
        $tokens = $request->user()->tokens()
            ->latest()
            ->get(['id', 'name', 'abilities', 'last_used_at', 'expires_at', 'created_at'])
            ->map(fn (PersonalAccessToken $token) => $this->summary($token));

        return response()->json(['data' => $tokens]);
    }

    public function store(Request $request): JsonResponse
    {
        $maxSeconds = (int) config('sanctum.token_max_seconds', 31536000);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'abilities' => ['required', 'array', 'min:1'],
            'abilities.*' => ['string', Rule::in(self::ABILITIES)],
            'expires_in' => ['sometimes', 'nullable', 'integer', 'min:60', 'max:'.$maxSeconds],
        ]);

        $expiresAt = now()->addSeconds((int) ($data['expires_in'] ?? 86400));
        $newToken = $request->user()->createToken(
            $data['name'],
            array_values(array_unique($data['abilities'])),
            $expiresAt
        );

        return response()->json([
            'data' => $this->summary($newToken->accessToken),
            'token' => $newToken->plainTextToken,
            'warning' => 'Copy this token now. It will not be shown again.',
        ], 201);
    }

    public function destroy(Request $request, PersonalAccessToken $token): JsonResponse
    {
        abort_unless(
            $token->tokenable_type === $request->user()::class
            && (int) $token->tokenable_id === (int) $request->user()->id,
            404
        );

        $token->delete();

        return response()->json(['message' => 'Access token revoked.']);
    }

    private function summary(PersonalAccessToken $token): array
    {
        return [
            'id' => $token->id,
            'name' => $token->name,
            'abilities' => $token->abilities,
            'last_used_at' => $token->last_used_at,
            'expires_at' => $token->expires_at,
            'created_at' => $token->created_at,
        ];
    }
}