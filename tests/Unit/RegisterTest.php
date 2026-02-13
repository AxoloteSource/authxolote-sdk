<?php

namespace Authxolote\Sdk\Tests\Unit;

use Authxolote\Sdk\Authxolote;
use Authxolote\Sdk\DTO\UserDto;
use Authxolote\Sdk\Tests\TestCase;
use Illuminate\Support\Facades\Http;

class RegisterTest extends TestCase
{
    /** @test */
    public function it_can_register_a_user()
    {
        Authxolote::fake();

        $user = Authxolote::register(
            'test@example.com',
            'Test User',
            'password123',
            'admin'
        );

        $this->assertInstanceOf(UserDto::class, $user);
        $this->assertEquals('test@example.com', $user->email);
        $this->assertNotEmpty($user->accessToken);
    }

    /** @test */
    public function it_returns_null_if_registration_fails()
    {
        Authxolote::fake(false); // Desactivar fake global para usar Http::fake manual
        
        Http::fake([
            'https://authxolote.test/api/api/v1/register' => Http::response(['message' => 'Error'], 422),
        ]);

        $user = Authxolote::register(
            'test@example.com',
            'Test User',
            'password123',
            'admin'
        );

        $this->assertNull($user);
    }
}
