<?php

namespace Authxolote\Sdk\DTO;

class OtpVerifyDto
{
    public function __construct(
        public bool $verified,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            (bool) ($data['data']['verified'] ?? false),
        );
    }
}
