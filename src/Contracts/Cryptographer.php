<?php

declare(strict_types=1);

namespace Sanderdekroon\GFEncryption\Contracts;

interface Cryptographer
{
    public function __construct(string $encryptionKey);
    public static function make(string $key): Cryptographer;
    public function encrypt($message): string;
    public function decrypt(string $encrypted): string;
}
