<?php

namespace Authxolote\Sdk\DTO;

class PasswordTokenDto
{
    public string $token;
    public string $expiresAt;
    public ?string $codeDebug;

    public function __construct(string $token, string $expiresAt, ?string $codeDebug = null)
    {
        $this->token = $token;
        $this->expiresAt = $expiresAt;
        $this->codeDebug = $codeDebug;
    }

    public static function fromArray(array $data): self
    {
        $responseData = $data['data'] ?? [];

        return new self(
            $responseData['token'],
            $responseData['expires_at'],
            $responseData['code_debug'] ?? null
        );
    }
}
