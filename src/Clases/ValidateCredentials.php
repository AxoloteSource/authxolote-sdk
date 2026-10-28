<?php

namespace Authxolote\Sdk\Clases;

use Authxolote\Sdk\Authxolote;
use Throwable;

class ValidateCredentials extends AuthxoloteBase
{
    /**
     * Usa el token de servicio configurado en `authxolote.token`.
     */
    protected bool $userAuthUserToken = false;

    public function __construct()
    {
        parent::__construct('/api/v1/validate-credentials');
    }

    /**
     * Valida las credenciales de un usuario sin generar un token de acceso.
     *
     * @return bool|null True si las credenciales son válidas, false si no lo son,
     *                   null si el servicio no respondió correctamente.
     */
    public function run(string $email, string $password): ?bool
    {
        if (Authxolote::isFake()) {
            return (bool) data_get($this->fakeResponse(), 'data.valid', true);
        }

        try {
            $response = $this->post([
                'email' => $email,
                'password' => $password,
            ]);
        } catch (Throwable) {
            return null;
        }

        if (! $response->successful()) {
            return null;
        }

        return (bool) data_get($response->json(), 'data.valid', false);
    }

    protected function fakeResponse(): array
    {
        return [
            'status' => 'OK',
            'message' => null,
            'data' => [
                'valid' => true,
            ],
        ];
    }
}
