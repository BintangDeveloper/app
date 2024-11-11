<?php

declare(strict_types=1);

namespace App\Helpers;

use DateTimeImmutable;
use Lcobucci\JWT\Encoding\ChainedFormatter;
use Lcobucci\JWT\Encoding\JoseEncoder;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Token\Builder;
use Lcobucci\JWT\Token\Parser;
use Lcobucci\JWT\Token\UnencryptedToken;
use Lcobucci\JWT\Validation\Constraint\RelatedTo;
use Lcobucci\JWT\Validation\Validator;

class JwtHelper
{
    private static Sha256 $algorithm;
    private static InMemory $signingKey;

    /**
     * Initializes the signing algorithm and key for token creation.
     *
     * @param string $secretKey Secret key for signing tokens.
     * @return void
     */
    public static function initialize(string $secretKey): void
    {
        self::$algorithm = new Sha256();
        self::$signingKey = InMemory::plainText($secretKey);
    }

    /**
     * Creates a new JWT token with specified claims and headers.
     *
     * @param array $claims Associative array of claims to be included in the token. Standard claims include:
     *                      'iss' (issuer), 'aud' (audience), 'sub' (subject), 'jti' (token ID), 'iat' (issued at),
     *                      'nbf' (not before), 'exp' (expiration time). Custom claims can also be added.
     * @param array $headers Associative array of headers to be included in the token.
     * @return string JWT token as a string.
     */
    public static function createToken(array $claims = [], array $headers = []): string
    {
        $tokenBuilder = new Builder(new JoseEncoder(), ChainedFormatter::default());
        $now = new DateTimeImmutable();

        $tokenBuilder = $tokenBuilder
            ->issuedBy($claims['iss'] ?? 'http://example.com')
            ->permittedFor($claims['aud'] ?? 'http://example.org')
            ->relatedTo($claims['sub'] ?? 'default-subject')
            ->identifiedBy($claims['jti'] ?? bin2hex(random_bytes(8)))
            ->issuedAt($now)
            ->canOnlyBeUsedAfter($now->modify('+1 minute'))
            ->expiresAt($now->modify('+1 hour'));

        // Add custom claims, excluding standard claims to avoid duplication.
        foreach ($claims as $name => $value) {
            if (!in_array($name, ['iss', 'aud', 'sub', 'jti', 'iat', 'nbf', 'exp'], true)) {
                $tokenBuilder = $tokenBuilder->withClaim($name, $value);
            }
        }

        // Add custom headers
        foreach ($headers as $name => $value) {
            $tokenBuilder = $tokenBuilder->withHeader($name, $value);
        }

        // Build and return the token as a string
        $token = $tokenBuilder->getToken(self::$algorithm, self::$signingKey);
        return $token->toString();
    }

    /**
     * Parses a JWT token string into a Token object.
     *
     * @param string $tokenString JWT token as a string.
     * @return UnencryptedToken|null Parsed token object, or null if parsing fails.
     */
    public static function parseToken(string $tokenString): ?UnencryptedToken
    {
        $parser = new Parser(new JoseEncoder());

        try {
            $token = $parser->parse($tokenString);
            return $token instanceof UnencryptedToken ? $token : null;
        } catch (\Exception $e) {
            // Handle invalid token structure or decode issues
            return null;
        }
    }

    /**
     * Validates a token against a specific subject claim.
     *
     * @param UnencryptedToken $token Token object to validate.
     * @param string $expectedSubject Expected subject ('sub' claim) for validation.
     * @return bool True if the token is valid for the given subject, otherwise false.
     */
    public static function validateToken(UnencryptedToken $token, string $expectedSubject): bool
    {
        $validator = new Validator();
        return $validator->validate($token, new RelatedTo($expectedSubject));
    }

    /**
     * Retrieves all claims from a parsed token as an associative array.
     *
     * @param UnencryptedToken $token Parsed token object.
     * @return array Associative array of all claims in the token.
     */
    public static function getClaims(UnencryptedToken $token): array
    {
        $claims = [];
        foreach ($token->claims()->all() as $name => $value) {
            $claims[$name] = $value;
        }
        return $claims;
    }

    /**
     * Retrieves all headers from a parsed token as an associative array.
     *
     * @param UnencryptedToken $token Parsed token object.
     * @return array Associative array of all headers in the token.
     */
    public static function getHeaders(UnencryptedToken $token): array
    {
        $headers = [];
        foreach ($token->headers()->all() as $name => $value) {
            $headers[$name] = $value;
        }
        return $headers;
    }
}
