<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class SessionTokenMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        try {
            JWTAuth::parseToken();
            $user = JWTAuth::authenticate();
            $token = (string) JWTAuth::getToken();

            if (! $user || $user->current_session_token !== $token) {
                return response()->json([
                    'success' => false,
                    'message' => 'Session expired. Please login again.',
                ], 401);
            }

            Auth::guard('api')->setUser($user);

            return $next($request);
        } catch (JWTException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Session expired. Please login again.',
            ], 401);
        }
    }
}
