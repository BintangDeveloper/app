<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Helpers\Response\JsonResponseHelper;
use Symfony\Component\HttpFoundation\Response;
use App\RsaKeyHandler;

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
        $token = $this->extractToken($request);

        if (!$token) {
            return JsonResponseHelper::error('Token is not set.', [], Response::HTTP_UNAUTHORIZED);
        }

        $rsa = new RsaKeyHandler(env('PRIVATE_KEY'));

        if (!$rsa->validateKeyPair($token)) {
            return JsonResponseHelper::error('Invalid token.', [ $token ], Response::HTTP_FORBIDDEN);
        }

        $request->attributes->set('claims', $token);

        return $next($request);
    }

    /**
     * Extract the token from the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    private function extractToken(Request $request): ?string
    {
        $authHeader = $request->header('Authorization');

        if ($authHeader && str_starts_with($authHeader, 'Bearer ')) {
            return substr($authHeader, 7);
        }

        return $request->query('token') ?? $request->cookie('token');
    }
}
