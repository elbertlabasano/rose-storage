<?php

namespace Tests\Feature;

use App\Models\User;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Tests\TestCase;

class RoseSsoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config([
            'sso.enabled' => true, 'sso.idcode_claim' => 'idcode',
            'services.keycloak' => [
                'client_id' => 'storage', 'client_secret' => 'test-client-secret',
                'base_url' => 'https://sso.rosebakeshop.net', 'realms' => 'jathnier',
                'redirect' => 'http://localhost/auth/keycloak/callback',
            ],
        ]);
    }

    private function localUser(string $code = '001234'): User
    {
        $user = User::factory()->create();
        $user->forceFill(['idcode' => $code])->save();
        return $user->fresh();
    }

    private function begin(): array
    {
        $response = $this->get('/auth/keycloak/redirect')->assertRedirect();
        parse_str(parse_url($response->headers->get('Location'), PHP_URL_QUERY), $query);
        return $query;
    }

    private function remote(array $claims, array &$history = []): void
    {
        $handler = HandlerStack::create(new MockHandler([
            new Response(200, [], json_encode(['access_token' => 'UPSTREAM_ACCESS_TOKEN', 'expires_in' => 300])),
            new Response(200, [], json_encode($claims)),
        ]));
        $handler->push(Middleware::history($history));
        Socialite::driver('keycloak')->setHttpClient(new Client(['handler' => $handler]));
    }

    public function test_redirect_uses_state_and_pkce_without_exposing_secret(): void
    {
        $query = $this->begin();
        $this->assertSame('storage', $query['client_id']);
        $this->assertSame('S256', $query['code_challenge_method']);
        $this->assertSame('code', $query['response_type']);
        $this->assertNotEmpty($query['state']);
        $this->assertSame(rtrim(strtr(base64_encode(hash('sha256', session('code_verifier'), true)), '+/', '-_'), '='), $query['code_challenge']);
        $this->assertArrayNotHasKey('client_secret', $query);
    }

    public function test_valid_sso_links_existing_user_and_uses_client_secret_post_and_pkce(): void
    {
        $user = $this->localUser();
        $bucket = $user->buckets()->create(['name' => 'existing-files']);
        $query = $this->begin();
        $verifier = session('code_verifier');
        $history = [];
        $this->remote(['sub' => 'rose-subject', 'idcode' => '001234', 'email' => 'different@example.test'], $history);
        $this->get('/auth/keycloak/callback?code=one-time-code&state='.$query['state'])->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user, 'web');
        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseHas('buckets', ['id' => $bucket->id, 'user_id' => $user->id]);
        $this->assertSame('rose-subject', $user->fresh()->keycloak_subject);
        $this->assertSame('https://sso.rosebakeshop.net/realms/jathnier', $user->fresh()->keycloak_issuer);
        parse_str((string) $history[0]['request']->getBody(), $fields);
        $this->assertSame('test-client-secret', $fields['client_secret']);
        $this->assertSame($verifier, $fields['code_verifier']);
        $this->assertSame('one-time-code', $fields['code']);
        $this->assertSame('Bearer UPSTREAM_ACCESS_TOKEN', $history[1]['request']->getHeaderLine('Authorization'));
        $response = $this->postJson('/auth/keycloak/session')->assertOk();
        $this->assertNotEmpty($response->json('token'));
        $this->assertStringNotContainsString('UPSTREAM_ACCESS_TOKEN', $response->getContent());
        $this->assertStringNotContainsString('test-client-secret', $response->getContent());
        $this->assertSame(['*'], $user->tokens()->first()->abilities);
    }

    public function test_invalid_state_is_denied_before_token_exchange(): void
    {
        $this->localUser();
        $this->begin();
        $history = [];
        $this->remote(['sub' => 'rose-subject', 'idcode' => '001234'], $history);
        $this->get('/auth/keycloak/callback?code=code&state=tampered')->assertRedirect('/login')->assertSessionHasErrors('sso');
        $this->assertGuest('web');
        $this->assertSame([], $history);
    }

    public function test_unknown_employee_code_is_not_provisioned_or_matched_by_email(): void
    {
        $user = $this->localUser();
        $query = $this->begin();
        $this->remote(['sub' => 'unlinked', 'idcode' => 'unknown', 'email' => $user->email]);
        $this->get('/auth/keycloak/callback?code=code&state='.$query['state'])->assertRedirect('/login')->assertSessionHasErrors('sso');
        $this->assertGuest('web');
        $this->assertDatabaseCount('users', 1);
        $this->assertNull($user->fresh()->keycloak_subject);
    }

    public function test_inactive_user_cannot_sign_in(): void
    {
        $user = $this->localUser(); $user->forceFill(['is_active' => false])->save();
        $query = $this->begin();
        $this->remote(['sub' => 'rose-subject', 'idcode' => '001234']);
        $this->get('/auth/keycloak/callback?code=code&state='.$query['state'])->assertRedirect('/login')->assertSessionHasErrors('sso');
        $this->assertGuest('web');
    }

    public function test_array_employee_claim_and_conflicting_subject_are_denied(): void
    {
        $user = $this->localUser();
        $query = $this->begin();
        $this->remote(['sub' => 'rose-subject', 'idcode' => ['001234']]);
        $this->get('/auth/keycloak/callback?code=code&state='.$query['state'])->assertRedirect('/login')->assertSessionHasErrors('sso');
        Socialite::forgetDrivers();
        $user->forceFill(['keycloak_issuer' => 'https://sso.rosebakeshop.net/realms/jathnier', 'keycloak_subject' => 'previous-subject'])->save();
        $query = $this->begin();
        $this->remote(['sub' => 'replacement-subject', 'idcode' => '001234']);
        $this->get('/auth/keycloak/callback?code=code&state='.$query['state'])->assertRedirect('/login')->assertSessionHasErrors('sso');
        $this->assertSame('previous-subject', $user->fresh()->keycloak_subject);
        $this->assertGuest('web');
    }

    public function test_expired_and_replayed_callbacks_are_denied(): void
    {
        $this->localUser();
        $query = $this->begin();
        $this->travel(11)->minutes();
        $this->get('/auth/keycloak/callback?code=code&state='.$query['state'])->assertRedirect('/login')->assertSessionHasErrors('sso');
        $this->assertGuest('web');
        $this->get('/auth/keycloak/callback?code=code&state='.$query['state'])->assertRedirect('/login')->assertSessionHasErrors('sso');
    }

    public function test_logout_revokes_only_local_sso_token_and_preserves_other_tokens(): void
    {
        $user = $this->localUser();
        $user->createToken('integration', ['objects:write']);
        $this->actingAs($user, 'web')->withSession(['rose_sso_authenticated' => true]);
        $this->postJson('/auth/keycloak/session')->assertOk();
        $this->assertDatabaseCount('personal_access_tokens', 2);
        $this->postJson('/auth/keycloak/logout')->assertOk();
        $this->assertGuest('web');
        $this->assertDatabaseCount('personal_access_tokens', 1);
        $this->assertSame('integration', $user->tokens()->first()->name);
    }

    public function test_guest_cannot_obtain_sso_token_and_entrypoints_respect_toggle(): void
    {
        $this->postJson('/auth/keycloak/session')->assertUnauthorized();
        config(['sso.enabled' => false]);
        $this->get('/auth/keycloak/redirect')->assertNotFound();
        $this->get('/auth/keycloak/callback')->assertNotFound();
    }
}
