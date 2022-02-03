<?php

declare(strict_types=1);

namespace Sanderdekroon\GFEncryption;

use Sanderdekroon\GFEncryption\Exceptions\CryptographicError;

class EncryptionKey
{
    /**
     * Path to the encryption key file.
     * @var string
     */
    protected $path;

    public function __construct(string $path)
    {
        $this->path = $path;
    }

    public static function fromDefaultPath(): EncryptionKey
    {
        return new self(dirname(ABSPATH) . '/.GRAVITY_FORMS_KEY');
    }

    /**
     * Return the contents of the encryption key, if any.
     * @return string
     * @throws CryptographicError If the key does not exist
     */
    public function get(): string
    {
        if (! $this->exists()) {
            throw new CryptographicError(__('Key file does not exist', 'sdk-gf-encrypt'));
        }

        $key = file_get_contents($this->getPath());
        if (empty($key)) {
            throw new CryptographicError(__('Key file is empty', 'sdk-gf-encrypt'));
        }

        return hex2bin(base64_decode($key));
    }

    /**
     * Return the path to the encryption key.
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * Wether or not the key exists on disk.
     * @return bool
     */
    public function exists(): bool
    {
        return file_exists($this->path);
    }

    /**
     * Create a new key on disk.
     * @return bool
     * @throws CryptographicError When unable to write
     */
    public function create(): bool
    {
        $this->prepareFile();

        if (! file_exists($this->path) || ! is_writable($this->path)) {
            throw new CryptographicError(__('Unable to write new encryption key', 'sdk-gf-encrypt'));
        }

        return file_put_contents($this->path, $this->generateKey()) !== false;
    }

    /**
     * Prepare and create the file and directory.
     * @return void
     */
    protected function prepareFile(): void
    {
        if (! file_exists($this->path)) {
            @mkdir(dirname($this->path), 0755, true);
            @touch($this->path);
        }
    }

    /**
     * Generate a random string
     * @return string
     */
    protected function generateKey(): string
    {
        return base64_encode(bin2hex(sodium_crypto_secretbox_keygen()));
    }
}
