<?php

namespace App\Helpers;

use DateTimeImmutable;
use Lcobucci\JWT\JwtFacade;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Validation\Constraint;
use Lcobucci\Clock\SystemClock;

class JwtTokenHelper
{
    protected static $jwtFacade = null;

    /**
     * Initialize the JwtFacade and key.
     *
     * @return JwtFacade
     */
    protected static function getJwtFacade(): JwtFacade
    {
        if (!self::$jwtFacade) {
            self::$jwtFacade = new JwtFacade();
        }
        return self::$jwtFacade;
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
        $key = InMemory::plainText(env('JWT_KEY', 'nokey'));
        $jwtFacade = self::getJwtFacade();

        // Generate token with claims and expiration
        $token = $jwtFacade->issue(
            new Sha256(),
            $key,
            static fn ($builder, $issuedAt) => $builder
                ->issuedBy(env('APP_URL', 'http://localhost'))
                ->expiresAt($issuedAt->modify("+{$ttl} seconds"))
                ->with(...array_map(static fn($k, $v) => [$k, $v], array_keys($claims), $claims))
        );

        return $token->toString();
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
            $key = InMemory::plainText(env('JWT_KEY', 'nokey'));
            $jwtFacade = self::getJwtFacade();
            $clock = new SystemClock(new \DateTimeZone('UTC'));

            // Parse and validate token
            $parsedToken = $jwtFacade->parse(
                $token,
                new Constraint\SignedWith(new Sha256(), $key),
                new Constraint\StrictValidAt($clock)
            );

            // Return claims if valid
            return $parsedToken->claims()->all();
        } catch (\Exception $e) {
            return null;
        }
    }
}
