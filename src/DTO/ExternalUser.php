<?php

namespace Authxolote\Sdk\DTO;

use Authxolote\Sdk\Factories\ExternalUserFactory;
use Authxolote\Sdk\Traits\HasActions;
use Illuminate\Contracts\Auth\Authenticatable;

class ExternalUser implements Authenticatable
{
    use HasActions;

    public UserDto $user;

    public static function factory(): ExternalUserFactory
    {
        return ExternalUserFactory::new();
    }

    public function __construct(array $data)
    {
        $this->user = UserDto::fromArray($data);
    }

    public function getAuthIdentifierName(): string
    {
        return 'id';
    }

    public function getAuthIdentifier()
    {
        return $this->user->id ?? null;
    }

    public function getAuthPasswordName(): string
    {
        return 'password';
    }

    public function getAuthPassword()
    {
        return '';
    }

    public function getRememberToken()
    {
        return $this->user->remember_token ?? null;
    }

    public function setRememberToken($value) {}

    public function getRememberTokenName(): string
    {
        return 'rememberToken';
    }

    public function __get($key)
    {
        return $this->user->$key ?? null;
    }

    public function __isset($key)
    {
        return isset($this->user->$key);
    }
}
