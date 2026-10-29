<?php

namespace Authxolote\Sdk\Clases;

use Authxolote\Sdk\Authxolote;
use Authxolote\Sdk\DTO\OtpChallengeDto;
use Illuminate\Support\Str;
use Throwable;

class CreateOtp extends AuthxoloteBase
{
    public function __construct()
    {
        parent::__construct('/api/v1/otp/challenges');
    }

    /**
     * Crea un reto OTP y lo envía al destino indicado.
     *
     * @return OtpChallengeDto|null Null si el servicio no respondió correctamente.
     */
    public function run(string $purpose, string $channel, string $destination): ?OtpChallengeDto
    {
        $this->error = [];

        if (Authxolote::isFake()) {
            $response = Authxolote::getFakeResponse(self::class, $this->fakeResponse());

            if (($response['status'] ?? null) === 'error') {
                $this->error = [
                    'status' => 'error',
                    'data' => $response['data'] ?? [],
                    'message' => $response['message'] ?? __('Error creating OTP'),
                ];

                return null;
            }

            return OtpChallengeDto::fromArray($response);
        }

        try {
            $response = $this->post([
                'purpose' => $purpose,
                'channel' => $channel,
                'destination' => $destination,
            ]);
        } catch (Throwable) {
            return null;
        }

        if (! $response->successful()) {
            return null;
        }

        return OtpChallengeDto::fromArray($response->json());
    }

    protected function fakeResponse(): array
    {
        return [
            'status' => 'OK',
            'message' => null,
            'data' => [
                'challenge_id' => (string) Str::uuid(),
                'expires_in' => 300,
                'resend_in' => 60,
                'channel' => 'email',
            ],
        ];
    }
}
