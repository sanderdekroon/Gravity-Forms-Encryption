<?php

namespace Sanderdekroon\GFEncryption\Tests\Unit;

use Mockery as m;
use Sanderdekroon\GFEncryption\Cryptographer;

class CryptographerTest extends TestCase
{
    private $encryptionKey;
    private $secretMessage;

    public function setUp(): void
    {
        parent::setUp();

        $this->encryptionKey = sodium_crypto_secretbox_keygen();
        $this->secretMessage = 'My secret message';
    }

    /** @test */
    public function crypt_can_encrypt_secret_message(): void
    {
        $cryptographer = new Cryptographer($this->encryptionKey);

        $encryptedMessage = $cryptographer->encrypt($this->secretMessage);

        $this->assertNotEquals($this->secretMessage, $encryptedMessage);
    }

    /** @test */
    public function crypt_can_decrypt_message(): void
    {
        $cryptographer = new Cryptographer($this->encryptionKey);

        $encryptedMessage = $cryptographer->encrypt($this->secretMessage);
        $decrypted = $cryptographer->decrypt($encryptedMessage);

        $this->assertEquals($this->secretMessage, $decrypted);
    }

    /** @test */
    public function encrypted_messages_are_unique(): void
    {
        $cryptographer = new Cryptographer($this->encryptionKey);

        $encryptedMessageA = $cryptographer->encrypt($this->secretMessage);
        $encryptedMessageB = $cryptographer->encrypt($this->secretMessage);

        $this->assertNotEquals($encryptedMessageA, $encryptedMessageB);
    }

    /** @test */
    public function encrypted_message_contains_nonce(): void
    {
        $cryptographer = new Cryptographer($this->encryptionKey);

        $encryptedMessage = $cryptographer->encrypt($this->secretMessage);
        [, $nonce] = explode('|', $encryptedMessage);

        $this->assertNotEmpty($nonce);
    }
}
