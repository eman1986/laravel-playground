<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Request;
use PHPOpenSourceSaver\JWTAuth\JWTAuth;
use Throwable;

class IsAuthenticated extends Middleware
{
    public function handle(Request $request, Closure $next)
    {
        try {
            JWTAuth::parseToken()->authenticate();
        } catch (Throwable $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $next($request);
    }
}
