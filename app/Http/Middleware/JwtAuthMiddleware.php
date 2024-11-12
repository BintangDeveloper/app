<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Helpers\JwtHelper;
use App\Helpers\ResponseHelper;
use Symfony\Component\HttpFoundation\Response;

use Barryvdh\Debugbar\Facades\Debugbar as DebugBar;

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
        // Initialize the JWT helper with the signing key
        JwtHelper::initialize(env('JWT_KEY', 'nokey'));

        // Retrieve the token from the Authorization header, query parameter, or cookie
        $authHeader = $request->header('Authorization');
        $token = null;

        if ($authHeader && str_starts_with($authHeader, 'Bearer ')) {
            $token = substr($authHeader, 7);
        } elseif ($request->query('token')) {
            $token = $request->query('token');
        } elseif ($request->cookie('token')) {
            $token = $request->cookie('token');
        }

        // If no token was found, return an error response
        if (!$token) {
            return ResponseHelper::error('Token not provided.', [], Response::HTTP_UNAUTHORIZED);
        }
        
        DebugBar::info($token);

        // Parse the token
        $parsedToken = JwtHelper::parseToken($token);
        if (!$parsedToken) {
            DebugBar::info($parsedToken);
            return ResponseHelper::error('Invalid token format.', [], Response::HTTP_UNAUTHORIZED);
        }

        // Validate the token's subject (sub) claim, adjust 'expected-subject' to your actual subject requirement
        $expectedSubject = hash('sha1', env('APP_NAME', 'APP'));  // replace this with your actual subject value
        if (!JwtHelper::validateToken($parsedToken, $expectedSubject)) {
            return ResponseHelper::error('Invalid or expired token.', [], Response::HTTP_UNAUTHORIZED);
        }

        // Retrieve claims from the token
        $claims = JwtHelper::getClaims($parsedToken);

        // Check if the 'permission' claim exists and is >= 2
        $permissionLevel = $claims['permission'] ?? 0; // Default to 0 if not present
        if ($permissionLevel < 2) {
            return ResponseHelper::error('Insufficient permissions.', [], Response::HTTP_FORBIDDEN);
        }

        // Attach the claims to the request object for further use
        $request->attributes->set('claims', $claims);

        return $next($request);
    }
}
