<?php

declare(strict_types=1);

namespace Sanderdekroon\GFEncryption;

use Sanderdekroon\GFEncryption\Exceptions\CryptographicError;

class Cryptographer implements Contracts\Cryptographer
{
    /**
     * The encryption key.
     * @var string
     */
    private $key;

    /**
     * Hide the encryption key if this object gets dumped.
     * @return array
     */
    public function __debugInfo(): array
    {
        return [];
    }

    public function __construct(string $encryptionKey)
    {
        $this->key = $encryptionKey;
    }

    public static function make(string $key): Contracts\Cryptographer
    {
        return new self($key);
    }

    /**
     * Encrypt a given message.
     * @param string $message
     * @return string
     */
    public function encrypt($message): string
    {
        $nonce = $this->generateNonce();
        $encrypted = sodium_crypto_secretbox((string) $message, $nonce, $this->key);
        $encoded = $this->encode($encrypted);

        return $encoded . '|' . $nonce;
    }

    /**
     * Decrypt the message into its original form.
     * @param string $encrypted
     * @return string
     * @throws CryptographicError When unable to decode
     */
    public function decrypt(string $encrypted): string
    {
        if (empty($encrypted)) {
            return $encrypted;
        }

        [$message, $nonce] = $this->splitMessageAndNonce($encrypted);
        $decoded = $this->decode($message);

        if (empty($decoded)) {
            return $encrypted;
        }

        $decrypted = sodium_crypto_secretbox_open($decoded, $nonce, $this->key);

        if ($decrypted === false) {
            throw new CryptographicError(__('Failed to decrypt data', 'sdk-gf-encrypt'));
        }

        return (string) $decrypted;
    }

    /**
     * Generate a new, random nonce.
     * @return string
     */
    protected function generateNonce(): string
    {
        $random = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);

        return (string) substr(bin2hex($random), 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
    }

    /**
     * Encode the given data to a base64 string representation.
     * @param string $data Binary data
     * @return string
     */
    protected function encode($data): string
    {
        return base64_encode(bin2hex($data));
    }

    /**
     * Decode a base64 message digest to binary data.
     * @param string $digest
     * @return string
     */
    protected function decode($digest): string
    {
        $decoded = hex2bin(base64_decode($digest));

        return $decoded ?: '';
    }

    /**
     * Split the given message into the actual message and the appended nonce.
     * @param string $message
     * @return array
     */
    protected function splitMessageAndNonce($message): array
    {
        if (strpos($message, '|') === false) {
            return [$message, ''];
        }

        return array_pad(explode('|', $message), 2, '');
    }
}
