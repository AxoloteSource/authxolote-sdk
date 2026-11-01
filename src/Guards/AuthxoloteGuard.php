<?php

namespace Authxolote\Sdk\Guards;

use App\Models\User;
use Authxolote\Sdk\Authxolote;
use Authxolote\Sdk\DTO\ExternalUser;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class AuthxoloteGuard implements Guard
{
    protected const CACHE_TTL_MINUTES = 10;

    protected ?Authenticatable $user = null;

    protected $request;
    protected $authxolote_url;
    protected $cache;
    protected $sync_user;

    public function __construct(
        Request $request,
        string $authxolote_url,
        bool $cache = true,
        bool $sync_user = true
    ) {
        $this->request = $request;
        $this->authxolote_url = $authxolote_url;
        $this->cache = $cache;
        $this->sync_user = $sync_user;
    }

    /**
     * Determines if the user is authenticated.
     */
    public function check(): bool
    {
        return ! is_null($this->user());
    }

    /**
     * Returns the instance of Authenticatable that represents the authenticated user.
     */
    public function user(): ?Authenticatable
    {
        if (! is_null($this->user)) {
            $token = $this->request->bearerToken();

            if (! $token || ! $this->isTokenExpired($token)) {
                return $this->user;
            }

            $this->user = null;
        }

        $token = $this->request->bearerToken();
        if (! $token || $this->isTokenExpired($token)) {
            return null;
        }

        $userData = $this->getUserData($token);

        if (! $userData) {
            return null;
        }

        if (! $this->sync_user) {
            $this->user = new ExternalUser($userData);

            return $this->user;
        }

        $this->user = User::where('external_user_id', $userData['data']['user']['id'])->first();
        if (! $this->user) {
            logger('AuthToken Valid but external_user_id not found in DB');

            return null;
        }

        return $this->user;
    }

    /**
     * Interface-required methods (setUser, etc.) if needed.
     */
    public function setUser(Authenticatable $user): void
    {
        $this->user = $user;
    }

    /**
     * Determines if the user is not authenticated.
     */
    public function guest(): bool
    {
        return ! $this->check();
    }

    /**
     * Returns the ID of the authenticated user.
     */
    public function id(): ?string
    {
        return $this->user() ? $this->user()->getAuthIdentifier() : null;
    }

    /**
     * Validates the provided credentials.
     */
    public function validate(array $credentials = []): bool
    {
        $token = $credentials['token'] ?? null;
        if (! $token) {
            return false;
        }

        $userData = $this->getUserData($token);

        return ! is_null($userData);
    }

    /**
     * Logic to fetch and cache user data from the external service.
     */
    protected function getUserData(string $token): ?array
    {
        if ($this->isTokenExpired($token)) {
            return null;
        }

        if (Authxolote::isFake()) {
            return Authxolote::me()->setAuthUserToken($token)->run();
        }

        $cacheKey = 'authxolote'.$token;
        $maxTtl = now()->addMinutes(self::CACHE_TTL_MINUTES);
        $expiration = $this->getTokenExpiration($token);
        $ttl = $expiration !== null
            ? $maxTtl->min(Carbon::createFromTimestamp($expiration))
            : $maxTtl;

        if ($ttl->isPast()) {
            return null;
        }

        if (! $this->cache) {
            return Authxolote::me()->setAuthUserToken($token)->run();
        }

        return Cache::remember(
            $cacheKey,
            $ttl,
            fn () => Authxolote::me()->setAuthUserToken($token)->run()
        );
    }

    /**
     * Returns the expiration timestamp of a JWT, when the token exposes one.
     */
    protected function getTokenExpiration(string $token): ?int
    {
        $parts = explode('.', $token);

        if (count($parts) !== 3) {
            return null;
        }

        $encodedPayload = strtr($parts[1], '-_', '+/');
        $padding = strlen($encodedPayload) % 4;

        if ($padding !== 0) {
            $encodedPayload .= str_repeat('=', 4 - $padding);
        }

        $decodedPayload = base64_decode($encodedPayload, true);

        if ($decodedPayload === false) {
            return null;
        }

        $claims = json_decode($decodedPayload, true);

        if (! is_array($claims) || ! isset($claims['exp']) || ! is_numeric($claims['exp'])) {
            return null;
        }

        return (int) $claims['exp'];
    }

    /**
     * Determines whether the JWT is past its expiration time.
     */
    protected function isTokenExpired(string $token): bool
    {
        $expiration = $this->getTokenExpiration($token);

        return $expiration !== null && $expiration <= now()->getTimestamp();
    }

    public function hasUser(): bool
    {
        return ! is_null($this->user);
    }
}
