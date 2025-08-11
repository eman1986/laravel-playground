<?php

namespace App\Services;

use App\Models\User;

class CacheService
{
    private const int TTL_SHORT = 3600;

    private const int TTL_LONG = 86400;

    public function getUserById(int $id): ?User
    {
        return cache()->remember('users', self::TTL_LONG, function () use ($id) {
            return User::find($id);
        });
    }
}
