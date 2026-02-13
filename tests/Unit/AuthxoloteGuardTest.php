<?php

namespace Authxolote\Sdk\Tests\Unit;

use Authxolote\Sdk\Authxolote;
use Authxolote\Sdk\Guards\AuthxoloteGuard;
use Authxolote\Sdk\DTO\ExternalUser;
use Authxolote\Sdk\Tests\TestCase;
use Illuminate\Http\Request;

class AuthxoloteGuardTest extends TestCase
{
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
}
