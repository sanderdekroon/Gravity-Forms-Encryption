<?php

namespace Sanderdekroon\GFEncryption;

use GF_Field;

class GravityForms
{
    /**
     * The implementation that can encrypt and decrypt data.
     * @var \Sanderdekroon\GFEncryption\Contracts\Cryptographer
     */
    protected $cryptographer;

    /**
     * The fields that are excluded from encryption.
     * @var array
     */
    protected $excludedFieldTypes = [
        \GF_Field_Total::class,
        \GF_Field_SingleProduct::class,
        \GF_Field_HiddenProduct::class,
    ];

    public function __construct(Contracts\Cryptographer $cryptographer)
    {
        $this->cryptographer = $cryptographer;
    }

    public function register(): void
    {
        add_filter('gform_save_field_value', [$this, 'encryptFieldValue'], PHP_INT_MAX, 3);
        add_filter('gform_get_field_value', [$this, 'decryptFieldValue'], PHP_INT_MIN, 3);
        add_filter('gform_get_input_value', [$this, 'decryptFieldValue'], PHP_INT_MIN, 3);
        add_filter('gplc_choice_counts', [$this, 'decryptChoiceCounts'], PHP_INT_MIN, 1);
    }

    /**
     * Encrypt the given field value.
     * @param mixed $value
     * @param array $entry
     * @param ?GF_Field $field
     * @return mixed
     */
    public function encryptFieldValue($value, $entry, ?GF_Field $field)
    {
        if (! $this->isValueEncrytable($value) || ! $this->isFieldEncryptable($field)) {
            return $value;
        }

        return $this->cryptographer->encrypt($value);
    }

    /**
     * Decrypt the field value.
     * @param mixed $value
     * @param array $entry
     * @param ?GF_Field $field
     * @return mixed
     */
    public function decryptFieldValue($value, array $entry, ?GF_Field $field)
    {
        if (! $this->isValueEncrytable($value) || ! $this->isFieldEncryptable($field)) {
            return $value;
        }

        if ($this->valueLooksEncrypted($value) && $this->entryIsEncrypted($entry)) {
            return $this->cryptographer->decrypt($value);
        }

        return $value;
    }

    /**
     * Decrypt the choice counts from the GP Limit Choices plugin.
     * @param  array $counts
     * @return array
     */
    public function decryptChoiceCounts($counts): array
    {
        $decrypted = [];
        foreach ($counts as $key => $value) {
            if ($this->isValueEncrytable($key) && $this->valueLooksEncrypted($key)) {
                $key = $this->cryptographer->decrypt($key);
            }

            if (!isset($decrypted[$key])) {
                $decrypted[$key] = 0;
            }

            $decrypted[$key] += $value;
        }

        return $decrypted;
    }

    protected function entryIsEncrypted(array $entry): bool
    {
        return apply_filters('gfencryption_entry_is_encrypted', true, $entry);
    }

    /**
     * Regex method to determine if the given value looks encrypted. It looks
     * it the first part is base64 encoded and the last part is hexadecimal.
     * @param string $value
     * @return bool
     */
    protected function valueLooksEncrypted(string $value): bool
    {
        $regex = sprintf(
            '/^(?:[A-Za-z0-9+\/]{4})*(?:[A-Za-z0-9+\/]{2}==|[A-Za-z0-9+\/]{3}=)?\|(?:[0-9a-fA-F]{%s})$/',
            SODIUM_CRYPTO_SECRETBOX_NONCEBYTES
        );

        return preg_match($regex, $value) === 1;
    }

    /**
     * Wether or not the given value can be en/decrypted.
     * @param  mixed  $value
     * @return bool
     */
    protected function isValueEncrytable($value)
    {
        $encryptable = ! empty($value) && is_scalar($value);

        return (bool) apply_filters('gfencryption_value_is_encryptable', $encryptable, $value);
    }

    /**
     * Wether or not the field can be en/decrypted.
     * @param  \GF_Field $field
     * @return bool
     */
    protected function isFieldEncryptable(?GF_Field $field): bool
    {
        $encryptable = $field
            && $this->isExcludedField($field) === false
            && $this->isProductOrPriceField($field) === false;

        return (bool) apply_filters('gfencryption_field_is_encryptable', $encryptable, $field);
    }

    /**
     * Check if the given GF_Field instance is excluded from encryption.
     * @param  \GF_Field $field
     * @return bool
     */
    protected function isExcludedField(GF_Field $field): bool
    {
        return in_array(get_class($field), $this->excludedFieldTypes);
    }

    /**
     * Check if the given GF_Field instance is a product or price enabled field.
     * @param  \GF_Field $field
     * @return bool
     */
    protected function isProductOrPriceField(GF_Field $field): bool
    {
        return !empty($field->productField) || $field->enablePrice;
    }
}
