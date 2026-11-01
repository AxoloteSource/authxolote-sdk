<?php

namespace Authxolote\Sdk\Tests\Unit;

use Authxolote\Sdk\Authxolote;
use Authxolote\Sdk\Guards\AuthxoloteGuard;
use Authxolote\Sdk\DTO\ExternalUser;
use Authxolote\Sdk\Tests\TestCase;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class AuthxoloteGuardTest extends TestCase
{
    protected function tearDown(): void
    {
        Carbon::setTestNow();
        Authxolote::fake(false);

        parent::tearDown();
    }

    /** @test */
    public function it_can_authenticate_a_user_with_token()
    {
        Authxolote::fake();
        
        $request = Request::create('/', 'GET');
        $request->headers->set('Authorization', 'Bearer fake-token');
        
        $guard = new AuthxoloteGuard($request, 'https://authxolote.test/api', true, false);
        
        $user = $guard->user();
        
        $this->assertInstanceOf(ExternalUser::class, $user);
        $this->assertTrue($guard->check());
    }

    /** @test */
    public function it_returns_null_if_no_token_is_provided()
    {
        $request = Request::create('/', 'GET');
        $guard = new AuthxoloteGuard($request, 'https://authxolote.test/api', true, false);
        
        $this->assertNull($guard->user());
        $this->assertFalse($guard->check());
    }

    /** @test */
    public function it_can_set_a_user_manually()
    {
        $request = Request::create('/', 'GET');
        $guard = new AuthxoloteGuard($request, 'https://authxolote.test/api', true, false);
        
        $user = new ExternalUser([
            'data' => [
                'user' => [
                    'id' => '123',
                    'name' => 'Manual User',
                    'email' => 'manual@example.com',
                    'role_id' => 'role-123',
                    'updated_at' => now()->toIso8601String(),
                    'created_at' => now()->toIso8601String(),
                    'remember_token' => null,
                ]
            ]
        ]);
        
        $guard->setUser($user);
        
        $this->assertEquals($user, $guard->user());
        $this->assertTrue($guard->check());
    }

    /** @test */
    public function it_rejects_an_expired_token_even_when_it_is_cached()
    {
        Authxolote::fake();
        config(['cache.default' => 'array']);
        Cache::flush();

        $token = $this->jwtToken(['exp' => now()->subMinute()->timestamp]);
        $request = Request::create('/', 'GET');
        $request->headers->set('Authorization', 'Bearer '.$token);

        Cache::put(
            'authxolote'.$token,
            ['data' => ['user' => ['id' => '123']]],
            now()->addMinutes(5)
        );

        $guard = new AuthxoloteGuard($request, 'https://authxolote.test/api', true, false);

        $this->assertNull($guard->user());
        $this->assertFalse($guard->check());
    }

    /** @test */
    public function it_does_not_keep_cached_user_data_past_the_token_expiration()
    {
        Authxolote::fake(false);
        config(['cache.default' => 'array']);
        Cache::flush();

        Http::fake([
            '*' => Http::response([
                'status' => 'OK',
                'message' => null,
                'data' => [
                    'user' => [
                        'id' => '123',
                        'name' => 'Test User',
                        'email' => 'test@example.com',
                        'role_id' => 'role-123',
                        'updated_at' => now()->toIso8601String(),
                        'created_at' => now()->toIso8601String(),
                        'remember_token' => null,
                    ],
                ],
            ]),
        ]);

        Carbon::setTestNow('2026-01-01 12:00:00');

        $token = $this->jwtToken(['exp' => now()->addSeconds(5)->timestamp]);
        $request = Request::create('/', 'GET');
        $request->headers->set('Authorization', 'Bearer '.$token);

        $guard = new AuthxoloteGuard($request, 'https://authxolote.test/api', true, false);

        $this->assertNotNull($guard->user());
        $this->assertTrue(Cache::has('authxolote'.$token));

        Carbon::setTestNow(now()->addSeconds(6));

        $this->assertFalse(Cache::has('authxolote'.$token));

        $nextRequestGuard = new AuthxoloteGuard($request, 'https://authxolote.test/api', true, false);

        $this->assertNull($nextRequestGuard->user());
    }

    /** @test */
    public function it_rejects_an_expired_token_in_validate()
    {
        Authxolote::fake();

        $token = $this->jwtToken(['exp' => now()->subMinute()->timestamp]);
        $request = Request::create('/', 'GET');
        $guard = new AuthxoloteGuard($request, 'https://authxolote.test/api', true, false);

        $this->assertFalse($guard->validate(['token' => $token]));
    }

    private function jwtToken(array $claims): string
    {
        $encode = static fn (array $data): string => rtrim(strtr(base64_encode(json_encode($data)), '+/', '-_'), '=');

        return $encode(['alg' => 'RS256', 'typ' => 'JWT']).'.'.$encode($claims).'.signature';
    }
}
