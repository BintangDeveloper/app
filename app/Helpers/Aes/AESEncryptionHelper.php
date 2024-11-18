<?php

namespace App\Helpers\Aes;

use phpseclib3\Crypt\AES;
use phpseclib3\Exception\BadDecryptionException;
use InvalidArgumentException;
use RuntimeException;

class AESEncryptionHelper
{
    private AES $aes;
    private bool $useBase64;
    private string $key;
    private string $cipher;

    /**
     * Constructor for dependency injection.
     *
     * @param string $key The encryption key
     * @param string $cipher AES cipher method ('aes-128-cbc', 'aes-192-cbc', 'aes-256-cbc')
     * @param bool $useBase64 Whether to base64 encode the encrypted output
     */
    public function __construct(string $key, string $cipher = 'aes-256-cbc', bool $useBase64 = true)
    {
        $this->configureCipher($cipher);
        $this->useBase64 = $useBase64;
        $this->setKey($key);
    }

    /**
     * Encrypts the provided data.
     *
     * @param string $data The plaintext data to encrypt
     * @return string Encrypted data (IV prepended and optionally base64 encoded)
     */
    public function encrypt(string $data): string
    {
        $iv = random_bytes($this->aes->getBlockLength() >> 3);
        $this->aes->setIV($iv);

        $encrypted = $this->aes->encrypt($data);
        if ($encrypted === false) {
            throw new RuntimeException('Encryption failed.');
        }

        $result = $iv . $encrypted;

        return $this->useBase64 ? base64_encode($result) : $result;
    }

    /**
     * Decrypts the provided encrypted data.
     *
     * @param string $data The encrypted data (IV prepended and optionally base64 encoded)
     * @return string Decrypted plaintext data
     */
    public function decrypt(string $data): string
    {
        if ($this->useBase64) {
            $data = base64_decode($data);
        }

        $ivLength = $this->aes->getBlockLength() >> 3;
        $iv = substr($data, 0, $ivLength);
        $encrypted = substr($data, $ivLength);

        $this->aes->setIV($iv);

        try {
            return $this->aes->decrypt($encrypted);
        } catch (BadDecryptionException $e) {
            throw new RuntimeException('Decryption failed: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Configures the AES cipher method.
     *
     * @param string $cipher The AES cipher method ('aes-128-cbc', 'aes-192-cbc', 'aes-256-cbc')
     */
    private function configureCipher(string $cipher): void
    {
        $supportedCiphers = ['aes-128-cbc', 'aes-192-cbc', 'aes-256-cbc'];
        if (!in_array($cipher, $supportedCiphers)) {
            throw new InvalidArgumentException("Invalid cipher method: $cipher");
        }

        $this->cipher = $cipher;
        $this->aes = new AES('cbc'); // Only CBC mode is supported in phpseclib
    }

    /**
     * Sets the encryption key.
     *
     * @param string $key The new encryption key
     */
    public function setKey(string $key): void
    {
        $keyLength = match ($this->cipher) {
            'aes-128-cbc' => 16,
            'aes-192-cbc' => 24,
            'aes-256-cbc' => 32,
        };

        $this->key = substr(hash('sha256', $key, true), 0, $keyLength);
        $this->aes->setKey($this->key);
    }

    /**
     * Enables or disables base64 encoding for encrypted output.
     *
     * @param bool $useBase64 Whether to use base64 encoding
     */
    public function useBase64Encoding(bool $useBase64): void
    {
        $this->useBase64 = $useBase64;
    }

    /**
     * Retrieves the current encryption key (for testing purposes).
     *
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * Retrieves the current AES cipher method (for testing purposes).
     *
     * @return string
     */
    public function getCipher(): string
    {
        return $this->cipher;
    }
}
