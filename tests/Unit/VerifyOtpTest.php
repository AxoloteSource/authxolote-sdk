<?php

namespace Authxolote\Sdk\Tests\Unit;

use Authxolote\Sdk\Authxolote;
use Authxolote\Sdk\Clases\VerifyOtp;
use Authxolote\Sdk\DTO\OtpVerifyDto;
use Authxolote\Sdk\Tests\TestCase;
use Illuminate\Support\Facades\Http;

class VerifyOtpTest extends TestCase
{
    /** @test */
    public function it_returns_an_instance_of_verify_otp()
    {
        $this->assertInstanceOf(VerifyOtp::class, Authxolote::verifyOtp());
    }

    /** @test */
    public function it_can_verify_an_otp_in_fake_mode()
    {
        Authxolote::fake();

        $verify = Authxolote::verifyOtp();
        $result = $verify->run('0195f51e-4a37-7087-b5f7-57d001537ab6', '123456');

        $this->assertInstanceOf(OtpVerifyDto::class, $result);
        $this->assertTrue($result->verified);
        $this->assertFalse($verify->hasError());
    }

    /** @test */
    public function it_returns_null_and_captures_the_error_in_fake_mode()
    {
        Authxolote::fake();

        Authxolote::fakeResponse(VerifyOtp::class, [
            'status' => 'error',
            'message' => 'Invalid OTP code.',
            'data' => [],
        ]);

        $verify = Authxolote::verifyOtp();
        $result = $verify->run('0195f51e-4a37-7087-b5f7-57d001537ab6', '000000');

        $this->assertNull($result);
        $this->assertTrue($verify->hasError());
        $this->assertSame('Invalid OTP code.', $verify->getError()['message']);
    }

    /** @test */
    public function it_returns_null_when_the_request_fails()
    {
        Authxolote::fake(false);

        Http::fake([
            '*/api/v1/otp/challenges/*/verify' => Http::response([
                'status' => 'error',
                'message' => 'Invalid OTP code.',
                'data' => [],
            ], 422),
        ]);

        $verify = Authxolote::verifyOtp();
        $result = $verify->run('0195f51e-4a37-7087-b5f7-57d001537ab6', '000000');

        $this->assertNull($result);
        $this->assertTrue($verify->hasError());
        $this->assertSame('Invalid OTP code.', $verify->getError()['message']);
    }

    /** @test */
    public function it_sends_the_expected_payload()
    {
        Authxolote::fake(false);

        Http::fake([
            '*/api/v1/otp/challenges/*/verify' => Http::response([
                'status' => 'OK',
                'message' => null,
                'data' => [
                    'verified' => true,
                ],
            ]),
        ]);

        $result = Authxolote::verifyOtp()->run('0195f51e-4a37-7087-b5f7-57d001537ab6', '123456');

        $this->assertInstanceOf(OtpVerifyDto::class, $result);
        $this->assertTrue($result->verified);

        Http::assertSent(function ($request) {
            return $request->url() === 'https://authxolote.test/api/api/v1/otp/challenges/0195f51e-4a37-7087-b5f7-57d001537ab6/verify'
                && $request->hasHeader('Authorization', 'Bearer fake-token')
                && $request['code'] === '123456';
        });
    }
}
