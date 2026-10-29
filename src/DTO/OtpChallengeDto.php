<?php

namespace Authxolote\Sdk\DTO;

class OtpChallengeDto
{
    public function __construct(
        public string $challengeId,
        public int $expiresIn,
        public int $resendIn,
        public string $channel,
    ) {}

    public static function fromArray(array $data): self
    {
        $responseData = $data['data'] ?? [];

        return new self(
            (string) ($responseData['challenge_id'] ?? ''),
            (int) ($responseData['expires_in'] ?? 0),
            (int) ($responseData['resend_in'] ?? 0),
            (string) ($responseData['channel'] ?? ''),
        );
    }

    /**
     * @return array<string, string|int>
     */
    public function toArray(): array
    {
        return [
            'challenge_id' => $this->challengeId,
            'expires_in' => $this->expiresIn,
            'resend_in' => $this->resendIn,
            'channel' => $this->channel,
        ];
    }
}
