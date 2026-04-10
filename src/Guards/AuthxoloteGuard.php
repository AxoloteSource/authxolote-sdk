<?php

namespace Authxolote\Sdk\Guards;

use App\Models\User;
use Authxolote\Sdk\Authxolote;
use Authxolote\Sdk\DTO\ExternalUser;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AuthxoloteGuard implements Guard
{
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
            return $this->user;
        }

        $token = $this->request->bearerToken();
        if (! $token) {
            return null;
        }

        $userData = $this->getUserData($token);

        if (! $userData) {
            return null;
        }

        if (! $this->sync_user) {
            logger('Sync user disabled');
            logger($userData);
            $this->user = new ExternalUser($userData);

            return $this->user;
        }

        $this->user = User::where('external_user_id', $userData['data']['id'])->first();
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
        if (Authxolote::isFake()) {
             return Authxolote::me()->setAuthUserToken($token)->run();
        }

        $cacheKey = 'authxolote'.$token;

        $ttl = now()->addMinutes(10);

        if (! $this->cache) {
            return Authxolote::me()->setAuthUserToken($token)->run();
        }

        return Cache::remember(
            $cacheKey,
            $ttl,
            fn () => Authxolote::me()->setAuthUserToken($token)->run()
        );
    }

    public function hasUser(): bool
    {
        return ! is_null($this->user);
    }
}
