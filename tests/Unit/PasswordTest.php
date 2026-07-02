<?php

namespace Authxolote\Sdk\Tests\Unit;

use Authxolote\Sdk\Authxolote;
use Authxolote\Sdk\DTO\PasswordResetDto;
use Authxolote\Sdk\DTO\PasswordTokenDto;
use Authxolote\Sdk\Tests\TestCase;
use Illuminate\Support\Facades\Http;

class PasswordTest extends TestCase
{
    /** @test */
    public function it_can_request_password_recovery()
    {
        Authxolote::fake();

        $response = Authxolote::recoveryPassword('test@example.com');

        $this->assertInstanceOf(PasswordTokenDto::class, $response);
        $this->assertNotEmpty($response->token);
        $this->assertNotEmpty($response->expiresAt);
        
        if (config('app.env') !== 'production') {
            $this->assertNotEmpty($response->codeDebug);
        }
    }

    /** @test */
    public function it_can_request_password_change()
    {
        Authxolote::fake();

        $response = Authxolote::changePassword();

        $this->assertInstanceOf(PasswordTokenDto::class, $response);
        $this->assertNotEmpty($response->token);
        $this->assertNotEmpty($response->expiresAt);

        if (config('app.env') !== 'production') {
            $this->assertNotEmpty($response->codeDebug);
        }
    }

    /** @test */
    public function it_can_reset_password()
    {
        Authxolote::fake();

        $response = Authxolote::resetPassword(
            'valid-token',
            '123456',
            'new-password',
            'new-password'
        );

        $this->assertInstanceOf(PasswordResetDto::class, $response);
        $this->assertEquals('OK', $response->status);
        $this->assertEquals('Contraseña restablecida con éxito', $response->message);
    }

    /** @test */
    public function it_returns_null_if_recovery_fails()
    {
        Authxolote::fake(false);
        
        Http::fake([
            '*' => Http::response(['message' => 'Error'], 422),
        ]);

        $response = Authxolote::recoveryPassword('test@example.com');

        $this->assertNull($response);
    }
}
