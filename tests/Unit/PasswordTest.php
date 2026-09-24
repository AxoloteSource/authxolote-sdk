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

        $recovery = Authxolote::passwordRecovery();
        $response = $recovery->run('test@example.com');

        $this->assertInstanceOf(PasswordTokenDto::class, $response);
        $this->assertNotEmpty($response->token);
        $this->assertNotEmpty($response->expiresAt);

        if (config('app.env') !== 'production') {
            $this->assertNotEmpty($response->codeDebug);
        }

        $this->assertFalse($recovery->hasError());
        $this->assertEmpty($recovery->getError());
    }

    /** @test */
    public function it_can_request_password_change()
    {
        Authxolote::fake();

        $change = Authxolote::passwordChange();
        $response = $change->run();

        $this->assertInstanceOf(PasswordTokenDto::class, $response);
        $this->assertNotEmpty($response->token);
        $this->assertNotEmpty($response->expiresAt);

        if (config('app.env') !== 'production') {
            $this->assertNotEmpty($response->codeDebug);
        }

        $this->assertFalse($change->hasError());
        $this->assertEmpty($change->getError());
    }

    /** @test */
    public function it_can_reset_password()
    {
        Authxolote::fake();

        $reset = Authxolote::passwordReset();
        $response = $reset->run(
            'valid-token',
            '123456',
            'new-password',
            'new-password'
        );

        $this->assertInstanceOf(PasswordResetDto::class, $response);
        $this->assertEquals('OK', $response->status);
        $this->assertEquals('Contraseña restablecida con éxito', $response->message);

        $this->assertFalse($reset->hasError());
        $this->assertEmpty($reset->getError());
    }

    /** @test */
    public function it_returns_null_if_recovery_fails()
    {
        Authxolote::fake(false);

        Http::fake([
            '*' => Http::response(['message' => 'Error'], 422),
        ]);

        $recovery = Authxolote::passwordRecovery();
        $response = $recovery->run('test@example.com');

        $this->assertNull($response);
        $this->assertTrue($recovery->hasError());
        $this->assertSame('error', $recovery->getError()['status']);
        $this->assertSame('Error', $recovery->getError()['message']);
        $this->assertSame(['message' => 'Error'], $recovery->getError()['data']);
    }
}
