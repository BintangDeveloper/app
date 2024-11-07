<?php

namespace App\Helpers;

use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Token\Plain;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use DateTimeImmutable;
use Exception;

class JwtTokenHelper
{
    protected static $jwtConfig = null;

    /**
     * Initialize the JWT configuration (static initializer).
     *
     * @return Configuration
     */
    protected static function getJwtConfig(): Configuration
    {
        if (!self::$jwtConfig) {
            self::$jwtConfig = Configuration::forSymmetricSigner(
                new Sha256(),
                InMemory::plainText(env('JWT_KEY', 'nokey'))
            );
        }
        return self::$jwtConfig;
    }

    /**
     * Generate a JWT token with custom claims and expiration.
     *
     * @param  array  $claims  Custom claims to add to the token (e.g., ['user_id' => 1])
     * @param  int  $ttl  Time-to-live in seconds (e.g., 3600 for 1 hour)
     * @return string  The generated JWT token as a string
     */
    public static function generateToken(array $claims, int $ttl = 3600): string
    {
        $now = new DateTimeImmutable();
        $jwtConfig = self::getJwtConfig();

        $builder = $jwtConfig->builder()
            ->issuedBy(env('APP_URL', 'http://localhost'))     // Issuer of the token
            ->issuedAt($now)                       // Time of issuance
            ->expiresAt($now->modify("+{$ttl} seconds")); // Expiration time

        // Add custom claims
        foreach ($claims as $key => $value) {
            $builder->withClaim($key, $value);
        }

        // Create the token and return it as a string
        return $builder->getToken($jwtConfig->signer(), $jwtConfig->signingKey())->toString();
    }

    /**
     * Validate a JWT token and return its claims if valid.
     *
     * @param  string  $token  The JWT token string to validate
     * @return array|null  Claims if valid, null if invalid
     */
    public static function validateToken(string $token): ?array
    {
        try {
            $jwtConfig = self::getJwtConfig();

            // Parse the token
            $parsedToken = $jwtConfig->parser()->parse($token);

            assert($parsedToken instanceof Plain);

            // Validate the token signature and expiration
            if (!$jwtConfig->validator()->validate($parsedToken, ...$jwtConfig->validationConstraints())) {
                return null;
            }

            // Return the token claims as an array
            return $parsedToken->claims()->all();

        } catch (Exception $e) {
            // Handle any parsing or validation exceptions
            return null;
        }
    }
}
