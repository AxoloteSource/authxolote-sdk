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

        if (Authxolote::isFake()) {
            $this->fake($url);
        }

        $this->response = Http::withToken($this->token)
            ->withHeaders($this->headers)
            ->post($url, $data);

        return $this->response;
    }

    /**
     * @return PromiseInterface|Response
     */
    protected function get(?array $data = null, ?string $url = null)
    {
        $url ??= $this->url;

        if (Authxolote::isFake()) {
            $this->fake($url);
        }

        $this->response = Http::withToken($this->token)
            ->withHeaders($this->headers)
            ->get($url, $data);

        return $this->response;
    }

    /**
     * @return PromiseInterface|Response
     */
    protected function put(?array $data = null, ?string $url = null)
    {
        $url ??= $this->url;

        if (Authxolote::isFake()) {
            $this->fake($url);
        }

        $this->response = Http::withToken($this->token)
            ->withHeaders($this->headers)
            ->put($url, $data);

        return $this->response;
    }

    /**
     * @return PromiseInterface|Response
     */
    protected function deleteRequest(?array $data = null, ?string $url = null)
    {
        $url ??= $this->url;

        if (Authxolote::isFake()) {
            $this->fake($url);
        }

        $this->response = Http::withToken($this->token)
            ->withHeaders($this->headers)
            ->delete($url, $data);

        return $this->response;
    }

    protected function response(): ?array
    {
        return $this->response->json();
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
