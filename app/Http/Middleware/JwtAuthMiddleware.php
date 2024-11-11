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
        // Retrieve the token from the Authorization header
        $authHeader = $request->header('Authorization');
        $token = null;

        if ($authHeader && str_starts_with($authHeader, 'Bearer ')) {
            $token = substr($authHeader, 7); // Token from Authorization header
        } elseif ($request->query('token')) {
            $token = $request->query('token'); // Token from query parameter
        } elseif ($request->cookie('token')) {
            $token = $request->cookie('token'); // Token from cookie
        }

        // If no token was found, return an error response
        if (!$token) {
            return ResponseHelper::error('Token not provided.', [], Response::HTTP_UNAUTHORIZED);
        }

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
