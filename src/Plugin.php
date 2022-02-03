<?php

declare(strict_types=1);

namespace Sanderdekroon\GFEncryption;

use Throwable;

class Plugin
{
    public function boot(): void
    {
        try {
            $this->ensureLibSodium();
            $this->ensureEncryptionKey();

            $cryptographer = Cryptographer::make(EncryptionKey::fromDefaultPath()->get());

            $integration = new GravityForms($cryptographer);
            $integration->register();
        } catch (Exceptions\CryptographicError $e) {
            $this->abort($e->getMessage());
        } catch (Throwable $e) {
            $this->abort(__('Please consult the error logs for more information.', 'sdk-gf-encrypt'));

            error_log(sprintf(
                '%1$s "%2$s"',
                __('Unable to initialise GFEncryption:', 'sdk-gf-encrypt'),
                $e->getMessage()
            ));
        }
    }

    protected function ensureLibSodium()
    {
        if (! function_exists('sodium_crypto_secretbox')) {
            throw new Exceptions\CryptographicError(
                __('LibSodium does not seem to be installed', 'sdk-gf-encrypt')
            );
        }
    }

    protected function ensureEncryptionKey(): void
    {
        $encryptionKey = EncryptionKey::fromDefaultPath();

        if (! $encryptionKey->exists()) {
            $encryptionKey->create();
        }
    }

    protected function abort(string $message): void
    {
        add_action('admin_notices', function () use ($message) {
            printf(
                '<div class="notice notice-error"><p>
                    <strong>Warning:</strong> 
                    %1$s "%2$s". 
                    This means your entries are currently not being encrypted!
                </p></div>',
                __('An error occured while initialising the Gravity Forms Encryption plugin:', 'sdk-gf-encrypt'),
                esc_html($message)
            );
        });
    }
}
