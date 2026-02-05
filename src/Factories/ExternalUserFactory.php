<?php

namespace Authxolote\Sdk\Factories;

use Authxolote\Sdk\DTO\ExternalUser;
use Authxolote\Sdk\Enums\RoleEnum;
use Illuminate\Support\Str;

class ExternalUserFactory
{
    protected array $attributes = [];

    public function __construct()
    {
        $this->attributes = [
            'data' => [
                'user' => [
                    'id' => (string) Str::uuid(),
                    'name' => 'Fake User',
                    'email' => 'fake@example.com',
                    'role_id' => RoleEnum::Admin->value,
                    'updated_at' => now()->toDateTimeString(),
                    'created_at' => now()->toDateTimeString(),
                    'remember_token' => Str::random(10),
                ],
                'access_token' => Str::random(40),
            ],
        ];
    }

    public static function new(): self
    {
        return new self;
    }

    public function withRole(RoleEnum $role): self
    {
        $this->attributes['data']['user']['role_id'] = $role->value;

        return $this;
    }

    public function withAttributes(array $attributes): self
    {
        $this->attributes['data']['user'] = array_merge($this->attributes['data']['user'], $attributes);

        return $this;
    }

    public function create(array $attributes = []): ExternalUser
    {
        if (! empty($attributes)) {
            $this->withAttributes($attributes);
        }

        return new ExternalUser($this->attributes);
    }
}
