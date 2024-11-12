<?php

declare(strict_types=1);

namespace App\Helpers;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use DateTimeImmutable;
use Exception;

class JwtHelper
{
    private static string $secretKey;

    /**
     * Initializes the signing key for token creation.
     *
     * @param string $secretKey Secret key for signing tokens.
     * @return void
     */
    public static function initialize(string $secretKey): void
    {
        self::$secretKey = $secretKey;
    }

    /**
     * Creates a new JWT token with specified claims.
     *
     * @param array $claims Associative array of claims to be included in the token. Standard claims include:
     *                      'iss' (issuer), 'aud' (audience), 'sub' (subject), 'jti' (token ID), 'iat' (issued at),
     *                      'nbf' (not before), 'exp' (expiration time). Custom claims can also be added.
     * @return string JWT token as a string.
     */
    public static function createToken(array $claims = []): string
    {
        $now = new DateTimeImmutable();

        // Define standard claims with defaults
        $payload = array_merge([
            'iss' => $claims['iss'] ?? 'http://example.com',
            'aud' => $claims['aud'] ?? 'http://example.org',
            'sub' => $claims['sub'] ?? 'default-subject',
            'jti' => $claims['jti'] ?? bin2hex(random_bytes(8)),
            'iat' => $now->getTimestamp(),
            'nbf' => $now->modify('+1 minute')->getTimestamp(),
            'exp' => $now->modify('+1 hour')->getTimestamp()
        ], $claims);

        // Encode and return the token as a string
        return JWT::encode($payload, self::$secretKey, 'HS256');
    }

    /**
     * Parses and decodes a JWT token string.
     *
     * @param string $tokenString JWT token as a string.
     * @return object|null Parsed token payload, or null if parsing fails.
     */
    public static function parseToken(string $tokenString)
    {
        try {
            return JWT::decode($tokenString, new Key(self::$secretKey, 'HS256'));
        } catch (Exception $e) {
            // Handle invalid token structure or decode issues
            \Barryvdh\Debugbar\Facades\Debugbar::info($e->getMessage());
            return null;
        }
    }

    /**
     * Validates a token against a specific subject claim.
     *
     * @param object $decodedToken Decoded token payload to validate.
     * @param string $expectedSubject Expected subject ('sub' claim) for validation.
     * @return bool True if the token is valid for the given subject, otherwise false.
     */
    public static function validateToken(object $decodedToken, string $expectedSubject): bool
    {
        return isset($decodedToken->sub) && $decodedToken->sub === $expectedSubject;
    }

    /**
     * Retrieves all claims from a parsed token as an associative array.
     *
     * @param object $decodedToken Decoded token payload.
     * @return array Associative array of all claims in the token.
     */
    public static function getClaims(object $decodedToken): array
    {
        return (array) $decodedToken;
    }
}
