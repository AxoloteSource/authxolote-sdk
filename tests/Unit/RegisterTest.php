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

        $register = Authxolote::signUp();
        $user = $register->signUp(
            'test@example.com',
            'Test User',
            'password123',
            'admin'
        );

        $this->assertInstanceOf(UserDto::class, $user);
        $this->assertEquals('test@example.com', $user->email);
        $this->assertNotEmpty($user->accessToken);
        $this->assertFalse($register->hasError());
        $this->assertEmpty($register->getError());
    }

    /** @test */
    public function it_returns_null_if_registration_fails()
    {
        Authxolote::fake(false); // Desactivar fake global para usar Http::fake manual

        Http::fake([
            'https://authxolote.test/api/api/v1/register' => Http::response(['message' => 'Error'], 422),
        ]);

        $register = Authxolote::signUp();
        $user = $register->signUp(
            'test@example.com',
            'Test User',
            'password123',
            'admin'
        );

        $this->assertNull($user);
        $this->assertTrue($register->hasError());
        $this->assertSame('error', $register->getError()['status']);
        $this->assertSame('Error', $register->getError()['message']);
        $this->assertSame(['message' => 'Error'], $register->getError()['data']);
    }

    /** @test */
    public function it_keeps_the_deprecated_register_method_working()
    {
        Authxolote::fake();

        $user = Authxolote::register(
            'test@example.com',
            'Test User',
            'password123',
            'admin'
        );

        $this->assertInstanceOf(UserDto::class, $user);
    }
}
