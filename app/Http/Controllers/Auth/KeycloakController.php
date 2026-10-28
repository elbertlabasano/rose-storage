<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class KeycloakController extends Controller
{
    public function redirect(Request $request)
    {
        abort_unless(config('sso.enabled'), 404);
        try {
            $base = rtrim((string) config('services.keycloak.base_url'), '/');
            abort_unless(str_starts_with($base, 'https://') && filled(config('services.keycloak.client_id'))
                && filled(config('services.keycloak.client_secret')) && filled(config('services.keycloak.redirect')), 503);
            $request->session()->put('rose_sso_started_at', now()->timestamp);
            return Socialite::driver('keycloak')->setRequest($request)->scopes(['openid', 'profile', 'email'])->enablePKCE()->redirect();
        } catch (Throwable $exception) {
            Log::warning('Rose SSO redirect failed.', ['exception_class' => $exception::class]);
            return redirect('/login')->withErrors(['sso' => 'Rose Account sign-in is unavailable. Try again or use your storage credentials.']);
        }
    }

    public function callback(Request $request)
    {
        abort_unless(config('sso.enabled'), 404);
        try {
            $started = $request->session()->pull('rose_sso_started_at');
            abort_unless(is_int($started) && $started <= now()->timestamp && now()->timestamp - $started <= 600
                && ! $request->has('error') && is_string($request->query('code')) && $request->query('code') !== '', 403);
            // Stateful Socialite validates state and exchanges the code with PKCE.
            // UserInfo is fetched over verified HTTPS; no browser-supplied claims are trusted.
            $remote = Socialite::driver('keycloak')->setRequest($request)->scopes(['openid', 'profile', 'email'])->enablePKCE()->user();
            $code = $remote->getRaw()[config('sso.idcode_claim')] ?? null;
            $subject = $remote->getId();
            abort_unless(is_string($code) && $code !== '' && trim($code) === $code && strlen($code) <= 64
                && is_string($subject) && $subject !== '' && trim($subject) === $subject && strlen($subject) <= 191, 403);
            $issuer = rtrim(config('services.keycloak.base_url'), '/').'/realms/'.config('services.keycloak.realms');
            $user = DB::transaction(function () use ($issuer, $subject, $code) {
                $user = User::where('keycloak_issuer', $issuer)->where('keycloak_subject', $subject)->lockForUpdate()->first();
                if ($user) {
                    abort_unless($user->keycloak_issuer === $issuer && $user->keycloak_subject === $subject, 403);
                }
                if (! $user) {
                    $matches = User::where('idcode', $code)->lockForUpdate()->limit(2)->get();
                    abort_unless($matches->count() === 1, 403);
                    $user = $matches->first();
                    abort_unless($user->keycloak_issuer === null && $user->keycloak_subject === null, 403);
                }
                abort_unless($user->is_active === true && (string) $user->idcode === $code, 403);
                if ($user->keycloak_subject === null) {
                    $user->forceFill(['keycloak_issuer' => $issuer, 'keycloak_subject' => $subject])->save();
                }
                return $user;
            });
            Auth::guard('web')->login($user);
            $request->session()->regenerate();
            $request->session()->put('rose_sso_authenticated', true);
            return redirect('/dashboard');
        } catch (Throwable $exception) {
            // Never log token responses, authorization codes, employee claims or secrets.
            Log::warning('Rose SSO callback denied.', ['exception_class' => $exception::class]);
            return redirect('/login')->withErrors(['sso' => 'Rose Account sign-in could not complete. Your account must be linked to an active storage account. Contact your administrator.']);
        } finally {
            $request->session()->forget(['state', 'code_verifier', 'rose_sso_started_at']);
        }
    }

    public function session(Request $request)
    {
        $user = Auth::guard('web')->user();
        abort_unless($request->session()->get('rose_sso_authenticated') === true && $user->is_active, 403);
        $oldToken = $request->session()->get('rose_sso_token_id');
        if ($oldToken) { $user->tokens()->whereKey($oldToken)->delete(); }
        $token = $user->createToken('rose-sso-browser', ['*'], now()->addMinutes(max(1, min(480, config('sso.token_lifetime_minutes')))));
        $request->session()->put('rose_sso_token_id', $token->accessToken->id);
        return response()->json(['user' => $user, 'token' => $token->plainTextToken])->header('Cache-Control', 'private, no-store');
    }

    public function logout(Request $request)
    {
        $user = Auth::guard('web')->user();
        if ($user && $id = $request->session()->get('rose_sso_token_id')) { $user->tokens()->whereKey($id)->delete(); }
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        // Local sign-out only: do not end Rose SSO or other applications' sessions.
        return response()->json(['message' => 'Signed out of Rose Storage.']);
    }
}
