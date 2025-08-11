<?php

namespace App\Http\Middleware;

use App\Services\JwtService;
use Closure;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Throwable;

class IsAuthenticated extends Middleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!$request->hasHeader('Authorization')) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $jwt = $request->bearerToken();

        if ($jwt === null) {
            return response()->json(['error' => 'No token was provided.'], 400);
        }

        $jwtService = new JwtService;

        try {
            if (!$jwtService->isTokenValid($jwt)) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $uid = $jwtService->parseJwt($jwt, 'uid');

            if (!Auth::validate(['uid' => $uid])) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }
        } catch (Throwable $e) {
            report($e);

            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $next($request);
    }
}
