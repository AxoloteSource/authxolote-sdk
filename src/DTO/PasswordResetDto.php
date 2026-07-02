<?php

namespace Authxolote\Sdk\DTO;

class PasswordResetDto
{
    public string $status;
    public string $message;

    public function __construct(string $status, string $message)
    {
        $this->status = $status;
        $this->message = $message;
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['status'],
            $data['message'] ?? ''
        );
    }
}
