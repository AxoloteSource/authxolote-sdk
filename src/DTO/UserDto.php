<?php

namespace Authxolote\Sdk\DTO;

readonly class UserDto
{
    public function __construct(
        public string $name,
        public string $email,
        public string $roleId,
        public string $id,
        public string $updatedAt,
        public string $createdAt,
        public string $accessToken,
        public ?string $rememberToken = null,
        public ?string $emailVerifiedAt = null,
    ) {}

    public static function fromArray(array $user, $accessToken = ''): self
    {
        $user = $user['data']['user'];

        return new self(
            name: $user['name'],
            email: $user['email'],
            roleId: $user['role_id'],
            id: $user['id'],
            updatedAt: $user['updated_at'],
            createdAt: $user['created_at'],
            accessToken: $accessToken,
            rememberToken: $user['remember_token'],
            emailVerifiedAt: $user['email_verified_at'] ?? null,
        );
    }
}
