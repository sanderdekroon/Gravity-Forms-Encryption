<?php

namespace Sanderdekroon\GFEncryption\Tests\Unit;

use WP_Mock;
use GF_Field;
use Sanderdekroon\GFEncryption\Cryptographer;
use Sanderdekroon\GFEncryption\GravityForms;

class GravityFormsTest extends TestCase
{
    private $cryptographer;
    private $secretMessage = 'My unencrypted message';

    public function setUp(): void
    {
        parent::setUp();

        $this->cryptographer = new Cryptographer(sodium_crypto_secretbox_keygen());
    }

    /** @test */
    public function gravity_forms_filters_are_added(): void
    {
        $gfIntegration = new GravityForms($this->cryptographer);

        WP_Mock::expectFilterAdded('gform_save_field_value', [$gfIntegration, 'encryptFieldValue'], PHP_INT_MAX, 3);
        WP_Mock::expectFilterAdded('gform_get_field_value', [$gfIntegration, 'decryptFieldValue'], PHP_INT_MIN, 3);
        WP_Mock::expectFilterAdded('gform_get_input_value', [$gfIntegration, 'decryptFieldValue'], PHP_INT_MIN, 3);
        WP_Mock::expectFilterAdded('gplc_choice_counts', [$gfIntegration, 'decryptChoiceCounts'], PHP_INT_MIN, 1);

        $gfIntegration->register();

        $this->assertTrue(true);
    }

    /**
     * @test
     * @dataProvider provideDefaultFieldInstance
     */
    public function gravity_forms_can_encrypt_field_value(GF_Field $field): void
    {
        $gfIntegration = new GravityForms($this->cryptographer);

        $encrypted = $gfIntegration->encryptFieldValue($this->secretMessage, [], $field);

        $this->assertNotEquals($this->secretMessage, $encrypted);
    }

    /**
     * @test
     * @dataProvider providyEntryArray
     */
    public function gravity_forms_can_decrypt_entry($entry, GF_Field $field): void
    {
        $gfIntegration = new GravityForms($this->cryptographer);

        $encrypted = $gfIntegration->encryptFieldValue($this->secretMessage, [], $field);
        $decrypted = $gfIntegration->decryptFieldValue($encrypted, $entry, $field);

        $this->assertEquals($this->secretMessage, $decrypted);
    }

    /**
     * @test
     * @dataProvider provideFieldInstanceWithPriceEnabled
     */
    public function gravity_forms_ignores_price_enabled_fields(GF_Field $field): void
    {
        $gfIntegration = new GravityForms($this->cryptographer);

        $encrypted = $gfIntegration->encryptFieldValue($this->secretMessage, [], $field);

        $this->assertEquals($this->secretMessage, $encrypted);
    }

    public function providyEntryArray(): array
    {
        return [
            'default entry array' => [
                [],
                $this->getDefaultFieldInstance()
            ]
        ];
    }

    public function provideDefaultFieldInstance(): array
    {
        return [
            'default field without price enabled' => [
                $this->getDefaultFieldInstance(),
            ]
        ];
    }

    public function provideFieldInstanceWithPriceEnabled(): array
    {
        return [
            'default field with price enabled' => [
                $this->getPriceEnabledFieldInstance(),
            ]
        ];
    }

    protected function getDefaultFieldInstance()
    {
        $field = $this->getMockBuilder(GF_Field::class)->getMock();
        $field->enablePrice = false;
        $field->productField = '';

        return $field;
    }

    protected function getPriceEnabledFieldInstance()
    {
        $field = $this->getDefaultFieldInstance();
        $field->enablePrice = true;

        return $field;
    }
}
