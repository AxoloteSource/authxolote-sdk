<?php

namespace Authxolote\Sdk\Tests\Unit;

use Authxolote\Sdk\Authxolote;
use Authxolote\Sdk\Clases\CreateOtp;
use Authxolote\Sdk\DTO\OtpChallengeDto;
use Authxolote\Sdk\Tests\TestCase;
use Illuminate\Support\Facades\Http;

class CreateOtpTest extends TestCase
{
    /** @test */
    public function it_returns_an_instance_of_create_otp()
    {
        $this->assertInstanceOf(CreateOtp::class, Authxolote::createOtp());
    }

    /** @test */
    public function it_can_create_an_otp_in_fake_mode()
    {
        Authxolote::fake();

        $otp = Authxolote::createOtp();
        $challenge = $otp->run('verify_email', 'email', 'user@example.com');

        $this->assertInstanceOf(OtpChallengeDto::class, $challenge);
        $this->assertNotEmpty($challenge->challengeId);
        $this->assertSame(300, $challenge->expiresIn);
        $this->assertSame(60, $challenge->resendIn);
        $this->assertSame('email', $challenge->channel);
        $this->assertFalse($otp->hasError());
    }

    /** @test */
    public function it_returns_null_and_captures_the_error_in_fake_mode()
    {
        Authxolote::fake();

        Authxolote::fakeResponse(CreateOtp::class, [
            'status' => 'error',
            'message' => 'Too many OTP requests. Please try again later.',
            'data' => [],
        ]);

        $otp = Authxolote::createOtp();
        $challenge = $otp->run('verify_email', 'email', 'user@example.com');

        $this->assertNull($challenge);
        $this->assertTrue($otp->hasError());
        $this->assertSame('Too many OTP requests. Please try again later.', $otp->getError()['message']);
    }

    /** @test */
    public function it_returns_null_when_the_request_fails()
    {
        Authxolote::fake(false);

        Http::fake([
            '*/api/v1/otp/challenges' => Http::response([
                'status' => 'error',
                'message' => 'The destination field is required.',
                'data' => ['destination' => ['The destination field is required.']],
            ], 422),
        ]);

        $otp = Authxolote::createOtp();
        $challenge = $otp->run('verify_email', 'email', 'user@example.com');

        $this->assertNull($challenge);
        $this->assertTrue($otp->hasError());
        $this->assertSame('The destination field is required.', $otp->getError()['message']);
    }

    /** @test */
    public function it_sends_the_expected_payload()
    {
        Authxolote::fake(false);

        Http::fake([
            '*/api/v1/otp/challenges' => Http::response([
                'status' => 'OK',
                'message' => null,
                'data' => [
                    'challenge_id' => '0195f51e-4a37-7087-b5f7-57d001537ab6',
                    'expires_in' => 300,
                    'resend_in' => 60,
                    'channel' => 'email',
                ],
            ], 201),
        ]);

        $challenge = Authxolote::createOtp()->run('verify_email', 'email', 'user@example.com');

        $this->assertInstanceOf(OtpChallengeDto::class, $challenge);
        $this->assertSame('0195f51e-4a37-7087-b5f7-57d001537ab6', $challenge->challengeId);

        Http::assertSent(function ($request) {
            return $request->url() === 'https://authxolote.test/api/api/v1/otp/challenges'
                && $request->hasHeader('Authorization', 'Bearer fake-token')
                && $request['purpose'] === 'verify_email'
                && $request['channel'] === 'email'
                && $request['destination'] === 'user@example.com';
        });
    }
}
