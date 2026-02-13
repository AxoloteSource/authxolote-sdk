<?php

namespace Authxolote\Sdk\Clases;

use Authxolote\Sdk\Authxolote;
use Authxolote\Sdk\DTO\UserDto;
use Faker\Factory;

class Register extends AuthxoloteBase
{
    protected bool $debugMode = true;

    public function __construct()
    {
        parent::__construct('/api/v1/register');
    }

    /**
     * Envía una petición para registrar un nuevo usuario.
     */
    public function register(string $email, string $name, string $password, string $roleKey): ?UserDto
    {
        try {
            if (Authxolote::isFake()) {
                $data = $this->fakeResponse();
                $data['data']['user']['email'] = $email;
                $data['data']['user']['name'] = $name;
                return UserDto::fromArray($data, $data['data']['access_token'] ?? '');
            }

            $response = $this->post([
                'email' => $email,
                'name' => $name,
                'password' => $password,
                'password_confirmation' => $password,
                'role_key' => $roleKey,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return UserDto::fromArray($data, $data['data']['access_token'] ?? '');
            }

            if ($this->debugMode) {
                logger()->error('Error al registrar usuario', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
            }

            return null;
        } catch (\Exception $e) {

            if ($this->debugMode) {
                logger()->error('Excepción al registrar usuario', ['exception' => $e]);
            }

            return null;
        }
    }

    protected function fakeResponse(): array
    {
        $faker = Factory::create();

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
                'access_token' => $faker->regexify('[A-Za-z0-9]{300}'),
            ],
        ];

    }
}
