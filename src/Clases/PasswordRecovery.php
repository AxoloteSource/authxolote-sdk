<?php

namespace Authxolote\Sdk\Clases;

use Authxolote\Sdk\Authxolote;
use Authxolote\Sdk\DTO\PasswordTokenDto;

class PasswordRecovery extends AuthxoloteBase
{
    public function __construct()
    {
        parent::__construct('/api/v1/recovery-password');
    }

    public function run(string $email): ?PasswordTokenDto
    {
        if (Authxolote::isFake()) {
            return PasswordTokenDto::fromArray($this->fakeResponse());
        }

        $response = $this->post([
            'email' => $email,
        ]);

        if ($response->successful()) {
            return PasswordTokenDto::fromArray($response->json());
        }

        return null;
    }

    protected function fakeResponse(): array
    {
        $faker = \Faker\Factory::create();
        $data = [
            'token' => $faker->sha256(),
            'expires_at' => now()->addMinutes(60)->toIso8601String(),
        ];

        if (config('app.env') !== 'production') {
            $data['code_debug'] = $faker->numerify('######');
        }

        return [
            'status' => 'OK',
            'message' => null,
            'data' => $data,
        ];
    }
}
