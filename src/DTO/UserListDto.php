<?php

namespace Authxolote\Sdk\DTO;

use Authxolote\Sdk\Authxolote;

class UserListDto
{
    public string $id;
    public string $name;
    public ?string $slug;
    public ?string $description;
    public ?int $usersCount;
    public string $createdAt;
    public ?string $updatedAt;

    public function __construct(
        string $id,
        string $name,
        ?string $slug = null,
        ?string $description = null,
        ?int $usersCount = null,
        string $createdAt,
        ?string $updatedAt = null
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->slug = $slug;
        $this->description = $description;
        $this->usersCount = $usersCount;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    public static function fromArray(array $data): self
    {
        $item = $data['data'] ?? $data;

        return new self(
            $item['id'],
            $item['name'],
            $item['slug'] ?? null,
            $item['description'] ?? null,
            $item['users_count'] ?? null,
            $item['created_at'],
            $item['updated_at'] ?? null
        );
    }

    public function users(
        ?int $page = 1,
        ?int $limit = 15,
        ?string $order = 'asc',
        ?array $filters = [],
        ?string $search = null
    ): ?array {
        return Authxolote::userList()->users(
            $this->id,
            $page,
            $limit,
            $order,
            $filters,
            $search
        );
    }
}
