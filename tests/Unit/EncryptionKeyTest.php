<?php

namespace Sanderdekroon\GFEncryption\Tests\Unit;

use Mockery as m;
use Sanderdekroon\GFEncryption\EncryptionKey;

class EncryptionKeyTest extends TestCase
{
    private $localPath;

    public function setUp(): void
    {
        parent::setUp();

        $this->localPath = dirname(__DIR__) . '/.UNIT_TEST_KEY';
    }

    /** @test */
    public function encryptionkey_path_is_correctly_set(): void
    {
        $encryptionKey = new EncryptionKey($this->localPath);

        $this->assertEquals($this->localPath, $encryptionKey->getPath());
    }

    /** @test */
    public function encryptionkey_does_not_exist_on_init(): void
    {
        $encryptionKey = new EncryptionKey($this->localPath);

        $this->assertFalse($encryptionKey->exists());
    }

    /** @test */
    public function encryptionkey_can_be_created(): void
    {
        $encryptionKey = new EncryptionKey($this->localPath);

        $encryptionKey->create();

        $this->assertTrue($encryptionKey->create());
        $this->assertFileExists($this->localPath);
    }

    /** @test */
    public function encryptionkey_is_valid(): void
    {
        $encryptionKey = new EncryptionKey($this->localPath);

        $encryptionKey->create();
        $key = $encryptionKey->get();

        $this->assertNotEmpty($key);
        $this->assertGreaterThanOrEqual(SODIUM_CRYPTO_SECRETBOX_KEYBYTES, strlen($key));
    }

    public function tearDown(): void
    {
        if (file_exists($this->localPath)) {
            unlink($this->localPath);
        }
    }
}
