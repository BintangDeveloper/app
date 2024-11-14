<?php

namespace App\Helpers\Encryption;

class AESEncryptionHelper
{
    private string $key;
    private string $cipher;
    private int $ivLength;
    private bool $useBase64;

    /**
     * Constructor for dependency injection.
     *
     * @param string $key The encryption key
     * @param string $cipher AES cipher method (default: 'aes-256-cbc')
     * @param bool $useBase64 Whether to base64 encode the encrypted output (default: true)
     */
    public function __construct(string $key, string $cipher = 'aes-256-cbc', bool $useBase64 = true)
    {
        if (!in_array($cipher, openssl_get_cipher_methods())) {
            throw new InvalidArgumentException("Invalid cipher method: $cipher");
        }
        
        $this->key = hash('sha256', $key, true); // Ensures 256-bit key length
        $this->cipher = $cipher;
        $this->ivLength = openssl_cipher_iv_length($cipher);
        $this->useBase64 = $useBase64;
    }

    /**
     * Encrypts the provided data.
     *
     * @param string $data The plaintext data to encrypt
     * @return string Encrypted data (with IV prepended and optionally base64 encoded)
     * @throws Exception if encryption fails
     */
    public function encrypt(string $data): string
    {
        $iv = openssl_random_pseudo_bytes($this->ivLength);

        $encrypted = openssl_encrypt($data, $this->cipher, $this->key, OPENSSL_RAW_DATA, $iv);
        if ($encrypted === false) {
            throw new RuntimeException('Encryption failed.');
        }

        $encryptedData = $iv . $encrypted; // Prepend IV for decryption

        return $this->useBase64 ? base64_encode($encryptedData) : $encryptedData;
    }

    /**
     * Decrypts the provided encrypted data.
     *
     * @param string $data The encrypted data (with prepended IV and optionally base64 encoded)
     * @return string Decrypted plaintext data
     * @throws Exception if decryption fails
     */
    public function decrypt(string $data): string
    {
        if ($this->useBase64) {
            $data = base64_decode($data);
        }

        $iv = substr($data, 0, $this->ivLength);
        $encrypted = substr($data, $this->ivLength);

        $decrypted = openssl_decrypt($encrypted, $this->cipher, $this->key, OPENSSL_RAW_DATA, $iv);
        if ($decrypted === false) {
            throw new RuntimeException('Decryption failed.');
        }

        return $decrypted;
    }

    /**
     * Sets the encryption key.
     *
     * @param string $key The new encryption key
     * @return void
     */
    public function setKey(string $key): void
    {
        $this->key = hash('sha256', $key, true);
    }

    /**
     * Sets the AES cipher method.
     *
     * @param string $cipher The AES cipher method (e.g., 'aes-128-cbc', 'aes-256-cbc')
     * @return void
     * @throws InvalidArgumentException for unsupported ciphers
     */
    public function setCipher(string $cipher): void
    {
        if (!in_array($cipher, openssl_get_cipher_methods())) {
            throw new InvalidArgumentException("Invalid cipher method: $cipher");
        }
        $this->cipher = $cipher;
        $this->ivLength = openssl_cipher_iv_length($cipher);
    }

    /**
     * Toggles base64 encoding for encrypted output.
     *
     * @param bool $useBase64 Whether to use base64 encoding
     * @return void
     */
    public function setBase64Encoding(bool $useBase64): void
    {
        $this->useBase64 = $useBase64;
    }
}

