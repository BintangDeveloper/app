<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Helpers\Response\JsonResponseHelper;
use Symfony\Component\HttpFoundation\Response;
use App\Helpers\Rsa\RSAKeyManager;
use App\Helpers\Aes\AESEncryptionHelper;
use RuntimeException;

class ApiAuthMiddleware
{
    /**
     * Handle an incoming request and validate the token.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        $aes = new AESEncryptionHelper(env('RSA_PASSPHRASE'));

        // Extract and decrypt the token
        $rawToken = base64_decode($this->extractToken($request));
        if (!$rawToken) {
            return JsonResponseHelper::error(
                'Token is missing or malformed.',
                [],
                Response::HTTP_UNAUTHORIZED
            );
        }

        try {
            $token = $aes->decrypt($rawToken);
        } catch (RuntimeException $e) {
            return JsonResponseHelper::error(
                'Failed to decrypt the token: ' . $e->getMessage(),
                [],
                Response::HTTP_UNAUTHORIZED
            );
        }

        if (!$token) {
            return JsonResponseHelper::error(
                'Token decryption returned invalid data.',
                [],
                Response::HTTP_UNAUTHORIZED
            );
        }

        // Validate the token using RSA
        $rsa = new RSAKeyManager(
            base64_decode(env('RSA_PRIVATE_KEY')),
            env('RSA_PASSPHRASE')
        );

        if (!$rsa->validatePublicKey($token)) {
            return JsonResponseHelper::error(
                'Invalid token.',
                ['encrypted_token' => $aes->encrypt($token)],
                Response::HTTP_FORBIDDEN
            );
        }

        // Set the token claims in the request
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
