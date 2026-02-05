<?php

namespace Authxolote\Sdk\Clases;

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
        $response = $this->post();

        if ($response->ok()) {
            return $response->json();
        }

        if ($response->serverError()) {
            abort(500, $response->body());
        }

        return null;
    }

    public function user(): ?User
    {
        $userData = $this->run();

        if (! $userData) {
            return null;
        }

        $user = User::where('external_user_id', $userData['data']['id'])->first();
        if (! $user) {
            logger('AuthToken Valid but external_user_id not found in DB');

            return null;
        }

        return $user;
    }
}
