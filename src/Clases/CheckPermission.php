<?php

namespace Authxolote\Sdk\Clases;

use Authxolote\Sdk\Authxolote;
use Authxolote\Sdk\Enums\Http;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Client\Response;

class CheckPermission extends AuthxoloteBase
{
    private static array $actionsFake = [];

    public static function actionsFake(array $actions): void
    {
        self::$actionsFake = $actions;
        Authxolote::fake();
    }

    protected bool $userAuthUserToken = true;

    protected array $actions;

    public function __construct(array $actions)
    {
        $this->actions = $actions;
        parent::__construct('/api/v1/is-allowed');
    }

    /**
     * Verifica si la acción única está permitida.
     * Asume que en $this->actions solo hay un elemento.
     */
    public function isAllow(): bool
    {
        $response = $this->post([
            'actions' => $this->actions,
        ]);

        $this->loggerResponse($response);
        $this->throwIfUnauthenticated($response);

        if ($response->successful()) {
            $body = $response->json();

            if (isset($body['data'])) {
                $action = $this->actions[0];

                $result = isset($body['data'][$action]) && $body['data'][$action] === true;
                $this->loggerSuccess($action, $result);

                return $result;
            }
        }

        $this->loggerError($response);

        return false;
    }

    /**
     * Verifica si todas las acciones en $this->actions están permitidas (true).
     */
    public function isAllowAll(): bool
    {
        $response = $this->post([
            'actions' => $this->actions,
        ]);

        $this->loggerResponse($response);
        $this->throwIfUnauthenticated($response);

        if ($response->successful()) {
            $body = $response->json();

            if (isset($body['data']) && is_array($body['data'])) {
                $allAllowed = collect($this->actions)->every(fn ($action) => ! empty($body['data'][$action]) && $body['data'][$action] === true);

                $this->loggerSuccess('all', $allAllowed);

                return $allAllowed;
            }
        }

        $this->loggerError($response);

        return false;
    }

    protected function fakeResponse(): array
    {
        return [
            'status' => 'OK',
            'message' => null,
            'data' => collect($this->actions)
                ->mapWithKeys(fn ($action) => [$action => in_array($action, self::$actionsFake)])
                ->toArray(),
        ];
    }

    /**
     * Un 401 del servicio de permisos significa que el token ya no es válido,
     * no que el usuario carezca del permiso. Se propaga para que el consumidor
     * responda 401 y el cliente pueda renovar la sesión.
     */
    private function throwIfUnauthenticated(Response $response): void
    {
        if ($response->status() === Http::Unauthorized->value) {
            $this->loggerError($response);

            throw new AuthenticationException('Unauthenticated.');
        }
    }

    /**
     * @param PromiseInterface|Response $response
     */
    private function loggerResponse($response): void
    {
        if ($this->debugMode) {
            logger()->info('Request sent to permissions API', [
                'url' => "$this->url",
                'actions' => $this->actions,
                'response_status' => $response->status(),
                'response_body' => $response->body(),
            ]);
        }
    }

    private function loggerSuccess(string $action, bool $result): void
    {
        if (! $this->debugMode) {
            return;
        }
        logger()->info('Permission check result', [
            'action' => $action,
            'allowed' => $result,
        ]);
    }

    /**
     * @param PromiseInterface|Response $response
     */
    private function loggerError($response): void
    {
        if (! $this->debugMode) {
            return;
        }

        logger()->warning('Permission check failed', [
            'url' => "$this->url",
            'actions' => $this->actions,
            'response_status' => $response->status(),
        ]);
    }
}
