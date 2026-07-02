<?php

namespace Authxolote\Sdk\Clases;

use Authxolote\Sdk\Authxolote;
use Authxolote\Sdk\DTO\PasswordResetDto;

class PasswordReset extends AuthxoloteBase
{
    public function __construct()
    {
        parent::__construct('/api/v1/reset-password');
    }

    public function run(string $token, string $otp_code, string $password, string $password_confirmation): ?PasswordResetDto
    {
        if (Authxolote::isFake()) {
            return PasswordResetDto::fromArray($this->fakeResponse());
        }

        $response = $this->post([
            'token' => $token,
            'otp_code' => $otp_code,
            'password' => $password,
            'password_confirmation' => $password_confirmation,
        ]);

        if ($response->successful()) {
            return PasswordResetDto::fromArray($response->json());
        }

        return null;
    }

    protected function fakeResponse(): array
    {
        return [
            'status' => 'OK',
            'message' => 'Contraseña restablecida con éxito',
            'data' => [],
        ];
    }
}
