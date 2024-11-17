<?php

namespace App\Helpers;

use InvalidArgumentException;
use RuntimeException;

class RsaKeyHandler
{
    private string $privateKey;
    private string $algorithm;
    private bool $isBase64;

    /**
     * Constructor to initialize the class with dependencies.
     *
     * @param string $privateKey PEM-formatted private key.
     * @param array $config Configuration options (e.g., 'algorithm' and 'isBase64').
     * @throws InvalidArgumentException
     */
    public function __construct(string $privateKey, array $config = [])
    {
        $this->algorithm = $config['algorithm'] ?? OPENSSL_ALGO_SHA256;
        $this->isBase64 = $config['isBase64'] ?? false;

        $processedKey = $this->isBase64
            ? $this->autocorrectPrivateKey($privateKey)
            : $this->ensureKeyFormat($privateKey, "PRIVATE");
            
        file_put_contents('gg.pem', $processedKey);

        if (!$this->isValidPrivateKey($processedKey)) {
            \Barryvdh\Debugbar\Facades\Debugbar::info($processedKey);
            throw new InvalidArgumentException("Invalid private key.");
        }

        $this->privateKey = $processedKey;
    }

    /**
     * Auto-corrects a Base64-encoded private key to valid PEM format.
     *
     * @param string $key Base64-encoded private key.
     * @return string PEM-formatted private key.
     * addPemHeaders
     */
    private function autocorrectPrivateKey(string $key): string
    {
        $key = str_replace(["\n", "\r", " "], "", preg_replace('/-----BEGIN (.*?)-----|-----END (.*?)-----/', '', $key));
        return $this->addPemHeaders(
            wordwrap($key, 64, "\n", true)
        , 'PRIVATE');
    }

    /**
     * Ensures a key (private or public) has a valid PEM header and footer.
     *
     * @param string $key The key string.
     * @param string $type Type of the key: "PRIVATE" or "PUBLIC".
     * @return string Properly formatted key.
     * @throws InvalidArgumentException
     */
    private function ensureKeyFormat(string $key, string $type): string
    {
        $key = preg_replace('/-----.*?-----/', PHP_EOL, trim($key)); // Remove existing headers/footers
        return $this->addPemHeaders(chunk_split($key, 64, "\n"), $type);
    }

    /**
     * Adds the appropriate PEM headers and footers to a key.
     *
     * @param string $key Key content without headers/footers.
     * @param string $type Type of the key: "PRIVATE" or "PUBLIC".
     * @return string PEM-formatted key.
     * @throws InvalidArgumentException
     */
    private function addPemHeaders(string $key, string $type): string
    {
        $headers = [
            "PRIVATE" => [
                "header" => "-----BEGIN ENCRYPTED PRIVATE KEY-----",
                "footer" => "-----END ENCRYPTED PRIVATE KEY-----",
            ],
            "PUBLIC" => [
                "header" => "-----BEGIN PUBLIC KEY-----",
                "footer" => "-----END PUBLIC KEY-----",
            ],
        ];

        if (!isset($headers[$type])) {
            throw new InvalidArgumentException("Invalid key type specified.");
        }

        return "{$headers[$type]['header']}\n$key{$headers[$type]['footer']}\n";
    }

    /**
     * Validates the private key format.
     *
     * @param string $key PEM-formatted private key.
     * @return bool True if valid, false otherwise.
     */
    private function isValidPrivateKey(string $key): bool
    {
        $process = openssl_pkey_get_private($key) !== false;
        
        \Barryvdh\Debugbar\Facades\Debugbar::info($process . openssl_error_string());
        return $process;
    }

    /**
     * Validates the public key format.
     *
     * @param string $key PEM-formatted public key.
     * @return bool True if valid, false otherwise.
     */
    private function isValidPublicKey(string $key): bool
    {
        return openssl_pkey_get_public($key, '@BINTANG0') !== false;
    }

    /**
     * Generates the public key from the private key.
     *
     * @return string PEM-formatted public key.
     * @throws RuntimeException
     */
    public function generatePublicKey(): string
    {
        $privateKeyResource = openssl_pkey_get_private($this->privateKey);

        if (!$privateKeyResource) {
            throw new RuntimeException("Failed to process the private key.");
        }

        $keyDetails = openssl_pkey_get_details($privateKeyResource);
        openssl_free_key($privateKeyResource);

        if (!$keyDetails || !isset($keyDetails['key'])) {
            throw new RuntimeException("Failed to extract public key.");
        }

        return $this->ensureKeyFormat($keyDetails['key'], "PUBLIC");
    }

    /**
     * Validates and auto-corrects the public key format.
     *
     * @param string $publicKey PEM-formatted public key.
     * @return string Properly formatted public key.
     * @throws InvalidArgumentException
     */
    public function validateAndCorrectPublicKey(string $publicKey): string
    {
        $correctedKey = $this->ensureKeyFormat($publicKey, "PUBLIC");

        if (!$this->isValidPublicKey($correctedKey)) {
            throw new InvalidArgumentException("Invalid public key.");
        }

        return $correctedKey;
    }

    /**
     * Validates if the given public key matches the private key.
     *
     * @param string $publicKey PEM-formatted public key.
     * @return bool True if valid, false otherwise.
     */
    public function validateKeyPair(string $publicKey): bool
    {
        $publicKey = $this->validateAndCorrectPublicKey($publicKey);
        $data = "test-data";

        $privateKeyResource = openssl_pkey_get_private($this->privateKey);
        if (!$privateKeyResource) {
            return false;
        }

        $signature = '';
        openssl_sign($data, $signature, $privateKeyResource, $this->algorithm);
        openssl_free_key($privateKeyResource);

        $publicKeyResource = openssl_pkey_get_public($publicKey);
        if (!$publicKeyResource) {
            return false;
        }

        $isValid = openssl_verify($data, $signature, $publicKeyResource, $this->algorithm) === 1;
        openssl_free_key($publicKeyResource);

        return $isValid;
    }
}
