<?php

namespace App\Helpers;

class RsaKeyHandler
{
    private string $privateKey;
    private string $algorithm;

    /**
     * Constructor to initialize the class with dependencies.
     *
     * @param string $privateKey PEM-formatted private key.
     * @param array $config Configuration options (e.g., algorithm).
     * @throws InvalidArgumentException
     */
    public function __construct(string $privateKey, array $config = [])
    {
        $this->algorithm = $config['algorithm'] ?? OPENSSL_ALGO_SHA256;

        $privateKey = $this->ensureKeyFormat($privateKey, "PRIVATE");

        if (!$this->isValidPrivateKey($privateKey)) {
            throw new InvalidArgumentException("Invalid private key.");
        }

        $this->privateKey = $privateKey;
    }

    /**
     * Ensures a key (private or public) has a valid PEM header and footer.
     *
     * @param string $key The key string.
     * @param string $type Type of the key: "PRIVATE" or "PUBLIC".
     * @return string Properly formatted key.
     */
    private function ensureKeyFormat(string $key, string $type): string
    {
        $headers = [
            "PRIVATE" => [
                "header" => "-----BEGIN RSA PRIVATE KEY-----",
                "footer" => "-----END RSA PRIVATE KEY-----"
            ],
            "PUBLIC" => [
                "header" => "-----BEGIN PUBLIC KEY-----",
                "footer" => "-----END PUBLIC KEY-----"
            ]
        ];

        if (!isset($headers[$type])) {
            throw new InvalidArgumentException("Invalid key type specified.");
        }

        $header = $headers[$type]['header'];
        $footer = $headers[$type]['footer'];

        // Remove any existing headers/footers
        $key = preg_replace('/-----.*?-----/', '', $key);
        $key = trim($key);

        // Re-add proper header and footer
        return $header . "\n" . chunk_split($key, 64, "\n") . $footer . "\n";
    }

    /**
     * Validates the private key format.
     *
     * @param string $key PEM-formatted private key.
     * @return bool True if valid, false otherwise.
     */
    private function isValidPrivateKey(string $key): bool
    {
        return openssl_pkey_get_private($key) !== false;
    }

    /**
     * Validates the public key format.
     *
     * @param string $key PEM-formatted public key.
     * @return bool True if valid, false otherwise.
     */
    private function isValidPublicKey(string $key): bool
    {
        return openssl_pkey_get_public($key) !== false;
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

        if ($privateKeyResource === false) {
            throw new RuntimeException("Failed to process the private key.");
        }

        $keyDetails = openssl_pkey_get_details($privateKeyResource);
        openssl_free_key($privateKeyResource);

        if ($keyDetails === false || !isset($keyDetails['key'])) {
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
        $publicKey = $this->ensureKeyFormat($publicKey, "PUBLIC");

        if (!$this->isValidPublicKey($publicKey)) {
            throw new InvalidArgumentException("Invalid public key.");
        }

        return $publicKey;
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
        $signature = '';

        // Sign the data with the private key
        $privateKeyResource = openssl_pkey_get_private($this->privateKey);
        if ($privateKeyResource === false) {
            return false;
        }
        openssl_sign($data, $signature, $privateKeyResource, $this->algorithm);
        openssl_free_key($privateKeyResource);

        // Verify the signature with the public key
        $publicKeyResource = openssl_pkey_get_public($publicKey);
        if ($publicKeyResource === false) {
            return false;
        }
        $isValid = openssl_verify($data, $signature, $publicKeyResource, $this->algorithm) === 1;
        openssl_free_key($publicKeyResource);

        return $isValid;
    }
}
