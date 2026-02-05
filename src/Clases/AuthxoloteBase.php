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

    private PromiseInterface|Response $response;

    public function __construct(protected string $uri)
    {
        $this->token = config('authxolote.token');
        $this->url = config('authxolote.url').$this->uri;
        $this->debugMode = config('authxolote.debug');
        if ($this->userAuthUserToken) {
            $this->setAuthUserToken();
        }
    }

    protected function post(?array $data = null): PromiseInterface|Response
    {
        if (Authxolote::isFake()) {
            $this->fake();
        }

        $this->response = Http::withToken($this->token)
            ->withHeaders($this->headers)
            ->post("$this->url", $data);

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

    private function fake(): void
    {
        Http::fake([
            $this->url = config('authxolote.url').$this->uri => Http::response(
                $this->fakeResponse(),
                200,
                $this->headers
            ),
        ]);
    }
}
