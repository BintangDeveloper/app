<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Helpers\Response\JsonResponseHelper;
use Symfony\Component\HttpFoundation\Response;

class ApiAuthMiddleware
{
    /**
     * Handle an incoming request and validate the token.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Retrieve the token from the Authorization header, query parameter, or cookie
        $authHeader = $request->header('Authorization');
        $token = null;

        if ($authHeader && str_starts_with($authHeader, 'Bearer ')) {
            $token = substr($authHeader, 7);
        } elseif ($request->query('token')) {
            $token = $request->query('token');
        } elseif ($request->cookie('token')) {
            $token = $request->cookie('token');
        } else {
          JsonResponseHelper::error('Token is not set.');
        }
        
        

        $request->attributes->set('claims', $token);

        return $next($request);
    }
}
