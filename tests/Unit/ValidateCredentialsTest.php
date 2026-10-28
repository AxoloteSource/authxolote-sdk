<?php

namespace Authxolote\Sdk\Tests\Unit;

use Authxolote\Sdk\Authxolote;
use Authxolote\Sdk\Clases\ValidateCredentials;
use Authxolote\Sdk\Tests\TestCase;
use Illuminate\Support\Facades\Http;

class ValidateCredentialsTest extends TestCase
{
    /** @test */
    public function it_returns_an_instance_of_validate_credentials()
    {
        $this->assertInstanceOf(ValidateCredentials::class, Authxolote::validateCredentials());
    }

    /** @test */
    public function it_returns_true_for_valid_credentials()
    {
        Authxolote::fake();

        $this->assertTrue(Authxolote::validateCredentials()->run('user@example.com', 'password'));
    }

    /** @test */
    public function it_returns_false_for_invalid_credentials_in_fake_mode()
    {
        Authxolote::fake();

        Authxolote::fakeResponse(ValidateCredentials::class, [
            'status' => 'OK',
            'message' => null,
            'data' => [
                'valid' => false,
            ],
        ]);

        $this->assertFalse(Authxolote::validateCredentials()->run('user@example.com', 'wrong-password'));
    }

    /** @test */
    public function it_returns_null_for_an_error_response_in_fake_mode()
    {
        Authxolote::fake();

        Authxolote::fakeResponse(ValidateCredentials::class, [
            'status' => 'error',
            'message' => 'Service unavailable',
            'data' => [],
        ]);

        $this->assertNull(Authxolote::validateCredentials()->run('user@example.com', 'password'));
    }

    /** @test */
    public function it_resets_custom_fake_responses_when_fake_is_called_again()
    {
        Authxolote::fake();

        Authxolote::fakeResponse(ValidateCredentials::class, [
            'data' => [
                'valid' => false,
            ],
        ]);

        Authxolote::fake();

        $this->assertTrue(Authxolote::validateCredentials()->run('user@example.com', 'password'));
    }

    /** @test */
    public function it_returns_false_for_invalid_credentials()
    {
        Authxolote::fake(false);

        Http::fake([
            '*/api/v1/validate-credentials' => Http::response([
                'status' => 'OK',
                'message' => null,
                'data' => [
                    'valid' => false,
                ],
            ]),
        ]);

        $this->assertFalse(Authxolote::validateCredentials()->run('user@example.com', 'wrong-password'));
    }

    /** @test */
    public function it_returns_null_when_the_request_fails()
    {
        Authxolote::fake(false);

        Http::fake([
            '*/api/v1/validate-credentials' => Http::response([], 500),
        ]);

        $this->assertNull(Authxolote::validateCredentials()->run('user@example.com', 'password'));
    }

    /** @test */
    public function it_sends_the_expected_payload()
    {
        Authxolote::fake(false);

        Http::fake([
            '*/api/v1/validate-credentials' => Http::response([
                'status' => 'OK',
                'message' => null,
                'data' => [
                    'valid' => true,
                ],
            ]),
        ]);

        Authxolote::validateCredentials()->run('user@example.com', 'password');

        Http::assertSent(function ($request) {
            return $request->url() === 'https://authxolote.test/api/api/v1/validate-credentials'
                && $request->hasHeader('Authorization', 'Bearer fake-token')
                && $request['email'] === 'user@example.com'
                && $request['password'] === 'password';
        });
    }
}
