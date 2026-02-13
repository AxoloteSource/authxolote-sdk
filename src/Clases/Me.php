<?php

namespace Authxolote\Sdk\Clases;

use Authxolote\Sdk\Authxolote;
use App\Models\User;

class Me extends AuthxoloteBase
{
    protected bool $userAuthUserToken = true;

    public function __construct()
    {
        parent::__construct('/api/v1/me');
    }

    public function run(): ?array
    {
        if (Authxolote::isFake()) {
            return $this->fakeResponse();
        }

        $response = $this->post();

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
        $faker = \Faker\Factory::create();

        return [
            'status' => 'OK',
            'message' => null,
            'data' => [
                'user' => [
                    'id' => $faker->uuid(),
                    'role_id' => $faker->uuid(),
                    'name' => $faker->name(),
                    'email' => $faker->safeEmail(),
                    'email_verified_at' => null,
                    'password' => bcrypt('password'),
                    'remember_token' => null,
                    'deleted_at' => null,
                    'created_at' => now()->toIso8601String(),
                    'updated_at' => now()->toIso8601String(),
                ],
            ],
        ];
    }
}
