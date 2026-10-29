<?php

namespace Authxolote\Sdk\Clases;

use Authxolote\Sdk\Authxolote;
use Authxolote\Sdk\DTO\OtpVerifyDto;
use Throwable;

class VerifyOtp extends AuthxoloteBase
{
    public function __construct()
    {
        parent::__construct('/api/v1/otp/challenges');
    }

    /**
     * Valida el código OTP de un reto.
     *
     * @return OtpVerifyDto|null Null si el servicio no respondió correctamente.
     */
    public function run(string $challengeId, string $code): ?OtpVerifyDto
    {
        $this->error = [];

        if (Authxolote::isFake()) {
            $response = Authxolote::getFakeResponse(self::class, $this->fakeResponse());

            if (($response['status'] ?? null) === 'error') {
                $this->error = [
                    'status' => 'error',
                    'data' => $response['data'] ?? [],
                    'message' => $response['message'] ?? __('Error verifying OTP'),
                ];

                return null;
            }

            return OtpVerifyDto::fromArray($response);
        }

        $url = rtrim($this->url, '/').'/'.rawurlencode($challengeId).'/verify';

        try {
            $response = $this->post(['code' => $code], $url);
        } catch (Throwable) {
            return null;
        }

        if (! $response->successful()) {
            return null;
        }

        return OtpVerifyDto::fromArray($response->json());
    }

    protected function fakeResponse(): array
    {
        return [
            'status' => 'OK',
            'message' => null,
            'data' => [
                'verified' => true,
            ],
        ];
    }
}
