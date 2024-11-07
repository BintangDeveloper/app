<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Helpers\JwtTokenHelper;
use App\Helpers\ResponseHelper;
use Symfony\Component\HttpFoundation\Response;

class JwtAuthMiddleware
{
    /**
     * Handle an incoming request and validate the JWT token.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Retrieve the Authorization header
        $authHeader = $request->header('Authorization');

        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return ResponseHelper::error('Token not provided.', [], Response::HTTP_UNAUTHORIZED);
        }

        // Extract the token from the header
        $token = substr($authHeader, 7);

        // Validate the token using JwtTokenHelper
        $claims = JwtTokenHelper::validateToken($token);

        if (!$claims) {
            return ResponseHelper::error('Invalid or expired token.', [], Response::HTTP_UNAUTHORIZED);
        }

        // Attach the claims to the request object for further use
        $request->attributes->set('claims', $claims);

        return $next($request);
    }
}
