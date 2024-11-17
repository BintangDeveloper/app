<?php

namespace App\Helpers\Rsa;

use phpseclib3\Crypt\RSA;

class RSAKeyManager
{
    private string $privateKeyPem;
    private ?string $passphrase;
    private ?RSA $privateKey = null;

    public function __construct(string $privateKey, ?string $passphrase = null)
    {
        $this->privateKeyPem = $privateKey;
        $this->passphrase = $passphrase;
        $this->loadPrivateKey();
    }

    /**
     * Load the private key from the file using the passphrase.
     *
     * @throws Exception
     */
    private function loadPrivateKey(): void
    {
        $keyContent = $this->privateKeyPem;
        
        try {
            // Load the private key with the passphrase if provided
            $this->privateKey = RSA::loadPrivateKey($keyContent, $this->passphrase);
        } catch (Exception $e) {
            throw new Exception("Failed to load private key: " . $e->getMessage() . "\n" . $keyContent);
        }
    }

    /**
     * Generate the public key from the private key.
     *
     * @return string
     * @throws Exception
     */
    public function generatePublicKey(): string
    {
        if (!$this->privateKey) {
            throw new Exception("Private key not loaded.");
        }

        return $this->privateKey->getPublicKey()->toString('PKCS8');
    }

    /**
     * Validate if the public key matches the private key.
     *
     * @param string $publicKey
     * @return bool
     */
    public function validatePublicKey(string $publicKey): bool
    {
        if (!$this->privateKey) {
            return false;
        }

        $generatedPublicKey = $this->privateKey->getPublicKey()->toString('PKCS8');
        return $generatedPublicKey === $publicKey;
    }
}

