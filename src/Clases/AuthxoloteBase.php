<?php

namespace Authxolote\Sdk\Clases;

use Authxolote\Sdk\Authxolote;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class AuthxoloteBase
{
    protected ?string $token;

    protected string $url;

    protected bool $debugMode = false;

    protected bool $userAuthUserToken = false;

    protected array $headers = ['Accept' => 'application/json'];

    protected string $uri;

    /**
     * Último error ocurrido durante la operación.
     */
    protected array $error = [];

    private $response;

    public function __construct(string $uri)
    {
        $this->uri = $uri;
        $this->token = config('authxolote.token');
        $this->url = rtrim(config('authxolote.url'), '/').$this->uri;
        $this->debugMode = config('authxolote.debug');
        if ($this->userAuthUserToken) {
            $this->setAuthUserToken();
        }
    }

    /**
     * @return PromiseInterface|Response
     */
    protected function post(?array $data = null, ?string $url = null)
    {
        $url ??= $this->url;
        $this->error = [];

        if (Authxolote::isFake()) {
            $this->fake($url);
        }

        try {
            $this->response = Http::withToken($this->token)
                ->withHeaders($this->headers)
                ->post($url, $data);
        } catch (\Throwable $e) {
            $this->captureError($e);
            throw $e;
        }

        if ($this->response instanceof Response && $this->response->failed()) {
            $this->captureError($this->response);
        }

        return $this->response;
    }

    /**
     * @return PromiseInterface|Response
     */
    protected function get(?array $data = null, ?string $url = null)
    {
        $url ??= $this->url;
        $this->error = [];

        if (Authxolote::isFake()) {
            $this->fake($url);
        }

        try {
            $this->response = Http::withToken($this->token)
                ->withHeaders($this->headers)
                ->get($url, $data);
        } catch (\Throwable $e) {
            $this->captureError($e);
            throw $e;
        }

        if ($this->response instanceof Response && $this->response->failed()) {
            $this->captureError($this->response);
        }

        return $this->response;
    }

    /**
     * @return PromiseInterface|Response
     */
    protected function put(?array $data = null, ?string $url = null)
    {
        $url ??= $this->url;
        $this->error = [];

        if (Authxolote::isFake()) {
            $this->fake($url);
        }

        try {
            $this->response = Http::withToken($this->token)
                ->withHeaders($this->headers)
                ->put($url, $data);
        } catch (\Throwable $e) {
            $this->captureError($e);
            throw $e;
        }

        if ($this->response instanceof Response && $this->response->failed()) {
            $this->captureError($this->response);
        }

        return $this->response;
    }

    /**
     * @return PromiseInterface|Response
     */
    protected function deleteRequest(?array $data = null, ?string $url = null)
    {
        $url ??= $this->url;
        $this->error = [];

        if (Authxolote::isFake()) {
            $this->fake($url);
        }

        try {
            $this->response = Http::withToken($this->token)
                ->withHeaders($this->headers)
                ->delete($url, $data);
        } catch (\Throwable $e) {
            $this->captureError($e);
            throw $e;
        }

        if ($this->response instanceof Response && $this->response->failed()) {
            $this->captureError($this->response);
        }

        return $this->response;
    }

    protected function response(): ?array
    {
        return $this->response->json();
    }

    /**
     * Indica si la última operación terminó con un error.
     */
    public function hasError(): bool
    {
        return ! empty($this->error);
    }

    /**
     * Retorna el último error ocurrido, o un arreglo vacío si no hubo error.
     */
    public function getError(): array
    {
        return $this->error;
    }

    /**
     * Registra el error de una respuesta fallida o de una excepción.
     */
    protected function captureError(\Throwable|Response $source): void
    {
        if ($source instanceof Response) {
            $payload = rescue(fn () => $source->json(), [], false);

            $this->error = [
                'status' => 'error',
                'data' => is_array($payload) ? $payload : [],
                'message' => is_array($payload) ? ($payload['message'] ?? $source->body()) : $source->body(),
            ];

            return;
        }

        $this->error = [
            'status' => 'error',
            'data' => [],
            'message' => $source->getMessage(),
        ];
    }

    /**
     * Activa el modo de depuración para la instancia actual.
     */
    public function activeDebugMode(): self
    {
        $this->debugMode = true;

        return $this;
    }

    /**
     * Actualiza el token de la instancia con el del usuario autenticado.
     */
    public function setAuthUserToken($token = null): self
    {
        if ($token) {
            $this->token = $token;

            return $this;
        }

        $this->token = request()->bearerToken();

        return $this;
    }

    protected function fakeResponse(): array
    {
        return [];
    }

    private function fake(?string $url = null): void
    {
        Http::fake([
            $url ?? $this->url => Http::response(
                $this->fakeResponse(),
                200,
                $this->headers
            ),
        ]);
    }
}
