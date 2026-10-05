<?php

namespace Tests\Feature;

use App\Models\Signal;
use App\Models\Source;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SsoIngestTest extends TestCase
{
    use RefreshDatabase;

    private function signedUrl(array $overrides = []): string
    {
        config(['services.sso.secret' => 'test-secret']);

        $params = array_merge([
            'email' => 'new@allocore.de',
            'name' => 'New User',
            'company' => 'acme',
            'role' => 'member',
            'ts' => time(),
        ], $overrides);

        $payload = implode('|', [
            $params['email'], $params['name'], $params['company'], $params['role'], $params['ts'],
        ]);
        $params['sig'] = hash_hmac('sha256', $payload, 'test-secret');

        return '/sso/allocore?'.http_build_query($params);
    }

    public function test_sso_creates_user_and_logs_in(): void
    {
        $this->get($this->signedUrl())->assertRedirect('/app');
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', ['email' => 'new@allocore.de', 'company_key' => 'acme', 'role' => 'member']);
    }

    public function test_sso_rejects_bad_signature(): void
    {
        $url = preg_replace('/sig=[0-9a-f]{64}/', 'sig='.str_repeat('0', 64), $this->signedUrl());
        $this->get($url)->assertForbidden();
    }

    public function test_sso_rejects_expired_timestamp(): void
    {
        $this->get($this->signedUrl(['ts' => time() - 3600]))->assertForbidden();
    }

    public function test_sso_links_existing_user(): void
    {
        User::create(['name' => 'Old', 'email' => 'new@allocore.de', 'password' => 'x', 'role' => 'allocore']);

        $this->get($this->signedUrl())->assertRedirect('/app');
        $this->assertEquals(1, User::where('email', 'new@allocore.de')->count());
        $this->assertEquals('acme', User::where('email', 'new@allocore.de')->first()->company_key);
    }

    public function test_ingest_stores_signal(): void
    {
        $source = Source::create(['name' => 'allocore', 'token' => 'tok123']);

        $this->postJson('/ingest/tok123', [
            'type' => 'audit.suggestion',
            'company_key' => 'acme',
            'payload' => ['ref_id' => '42', 'issue' => 'Missing SOP', 'solution' => 'Write SOP-7'],
        ])->assertOk()->assertJson(['ok' => true, 'created' => 1]);

        $this->assertDatabaseHas('signals', ['type' => 'audit.suggestion', 'company_key' => 'acme', 'source_id' => $source->id]);
    }

    public function test_ingest_rejects_bad_token(): void
    {
        $this->postJson('/ingest/nope', ['type' => 'x'])->assertUnauthorized();
    }

    public function test_ingest_batch(): void
    {
        Source::create(['name' => 'allocore', 'token' => 'tok123']);

        $this->postJson('/ingest/tok123', ['signals' => [
            ['type' => 'audit.suggestion', 'payload' => ['ref_id' => '1']],
            ['type' => 'audit.suggestion', 'payload' => ['ref_id' => '2']],
        ]])->assertJson(['created' => 2]);

        $this->assertEquals(2, Signal::count());
    }
}
