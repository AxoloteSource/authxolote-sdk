<?php

namespace Authxolote\Sdk\Clases;

use Authxolote\Sdk\Authxolote;
use Authxolote\Sdk\DTO\UserListDto;
use Faker\Factory;

class UserList extends AuthxoloteBase
{
    protected bool $userAuthUserToken = true;

    public function __construct()
    {
        parent::__construct('/api/v1/user-lists');
    }

    public function index(
        ?int $page = 1,
        ?int $limit = 15,
        ?string $order = 'asc',
        ?array $filters = [],
        ?string $search = null
    ): ?array {
        if (Authxolote::isFake()) {
            return $this->fakeResponse();
        }

        $response = $this->get(array_filter([
            'page' => $page,
            'limit' => $limit,
            'order' => $order,
            'filters' => $filters,
            'search' => $search,
        ], fn ($value) => ! is_null($value)));

        if ($response->ok()) {
            return $response->json();
        }

        if ($response->serverError()) {
            abort(500, $response->body());
        }

        return null;
    }

    public function store(
        string $name,
        ?string $slug = null,
        ?string $description = null,
        ?array $userIds = null
    ): ?UserListDto {
        if (Authxolote::isFake()) {
            $data = $this->fakeResponse();
            $data['data']['name'] = $name;

            return UserListDto::fromArray($data);
        }

        $response = $this->post(array_filter([
            'name' => $name,
            'slug' => $slug,
            'description' => $description,
            'user_ids' => $userIds,
        ], fn ($value) => ! is_null($value)));

        if ($response->ok()) {
            return UserListDto::fromArray($response->json());
        }

        if ($response->serverError()) {
            abort(500, $response->body());
        }

        return null;
    }

    public function show(string $id): ?UserListDto
    {
        $url = $this->url . '/' . $id;

        if (Authxolote::isFake()) {
            return UserListDto::fromArray($this->fakeResponse());
        }

        $response = $this->get(null, $url);

        if ($response->ok()) {
            return UserListDto::fromArray($response->json());
        }

        if ($response->serverError()) {
            abort(500, $response->body());
        }

        return null;
    }

    public function update(
        string $id,
        string $name,
        ?string $slug = null,
        ?string $description = null,
        ?array $userIds = null
    ): ?UserListDto {
        $url = $this->url . '/' . $id;

        if (Authxolote::isFake()) {
            $data = $this->fakeResponse();
            $data['data']['name'] = $name;

            return UserListDto::fromArray($data);
        }

        $response = $this->put(array_filter([
            'name' => $name,
            'slug' => $slug,
            'description' => $description,
            'user_ids' => $userIds,
        ], fn ($value) => ! is_null($value)), $url);

        if ($response->ok()) {
            return UserListDto::fromArray($response->json());
        }

        if ($response->serverError()) {
            abort(500, $response->body());
        }

        return null;
    }

    public function delete(string $id): bool
    {
        $url = $this->url . '/' . $id;

        if (Authxolote::isFake()) {
            return true;
        }

        $response = $this->deleteRequest(null, $url);

        if ($response->ok()) {
            return true;
        }

        if ($response->serverError()) {
            abort(500, $response->body());
        }

        return false;
    }

    public function users(
        string $id,
        ?int $page = 1,
        ?int $limit = 15,
        ?string $order = 'asc',
        ?array $filters = [],
        ?string $search = null
    ): ?array {
        $url = $this->url . '/' . $id . '/users';

        if (Authxolote::isFake()) {
            return $this->fakeResponse();
        }

        $response = $this->get(array_filter([
            'page' => $page,
            'limit' => $limit,
            'order' => $order,
            'filters' => $filters,
            'search' => $search,
        ], fn ($value) => ! is_null($value)), $url);

        if ($response->ok()) {
            return $response->json();
        }

        if ($response->serverError()) {
            abort(500, $response->body());
        }

        return null;
    }

    protected function fakeResponse(): array
    {
        $faker = Factory::create();

        return [
            'status' => 'OK',
            'message' => null,
            'data' => [
                'id' => $faker->uuid(),
                'name' => $faker->word(),
                'slug' => $faker->slug(),
                'description' => $faker->sentence(),
                'users_count' => $faker->numberBetween(0, 100),
                'created_at' => now()->toIso8601String(),
                'updated_at' => now()->toIso8601String(),
            ],
        ];
    }
}
