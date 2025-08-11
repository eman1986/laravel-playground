<?php

namespace App\Providers\Auth;

use App\Services\CacheService;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Guard;

class JwtGuard implements Guard
{
    protected ?Authenticatable $user = null;

    public function __construct(private readonly CacheService $cacheService) {}

    /**
     * Determine if the current user is authenticated.
     *
     * @return bool
     */
    public function check(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Determine if the current user is unauthenticated.
     *
     * @return bool
     */
    public function guest(): bool
    {
        return !$this->check();
    }

    /**
     * Get the currently authenticated user.
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function user(): ?Authenticatable
    {
        return $this->user ?? null;
    }

    /**
     * Get the ID for the currently authenticated user.
     *
     * @return int|null
     */
    public function id(): ?int
    {
        return $this->user()?->getAuthIdentifier();
    }

    /**
     * Validate a user's credentials.
     *
     * @param  array  $credentials
     * @return bool
     */
    public function validate(array $credentials = []): bool
    {
        if (empty($credentials['uid'])) {
            return false;
        }

        $user = $this->cacheService->getUserById($credentials['uid']);

        if ($user === null) {
            return false;
        }

        $this->setUser($user);

        return true;
    }

    /**
     * Determine if the guard has a user instance.
     *
     * @return bool
     */
    public function hasUser(): bool
    {
        return $this->user !== null;
    }

    /**
     * Set the current user.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @return \Illuminate\Contracts\Auth\Guard
     */
    public function setUser(Authenticatable $user): Guard
    {
        $this->user = $user;

        return $this;
    }
}
