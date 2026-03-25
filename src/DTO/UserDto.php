<?php

namespace Authxolote\Sdk\DTO;

class UserDto
{
    public string $name;
    public string $email;
    public string $roleId;
    public string $id;
    public string $updatedAt;
    public string $createdAt;
    public string $accessToken;
    public ?string $rememberToken;
    public ?string $emailVerifiedAt;

    public function __construct(
        string $name,
        string $email,
        string $roleId,
        string $id,
        string $updatedAt,
        string $createdAt,
        string $accessToken,
        ?string $rememberToken = null,
        ?string $emailVerifiedAt = null
    ) {
        $this->name = $name;
        $this->email = $email;
        $this->roleId = $roleId;
        $this->id = $id;
        $this->updatedAt = $updatedAt;
        $this->createdAt = $createdAt;
        $this->accessToken = $accessToken;
        $this->rememberToken = $rememberToken;
        $this->emailVerifiedAt = $emailVerifiedAt;
    }

    public static function fromArray(array $user, $accessToken = ''): self
    {
        $user = $user['data']['user'];

        return new self(
            $user['name'],
            $user['email'],
            $user['role_id'],
            $user['id'],
            $user['updated_at'],
            $user['created_at'],
            $accessToken,
            $user['remember_token'],
            $user['email_verified_at'] ?? null
        );
    }
}
