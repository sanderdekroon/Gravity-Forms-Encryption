<?php

/**
 * Plugin Name: Gravity Forms Encryption
 * Plugin URI: https://github.com/sanderdekroon/gravity-forms-encryption
 * Description: Encrypt all fields of the Gravity Forms entries
 * Version: 1.0.1
 * Author: sanderdekroon
 * Author URI: https://sanderdekroon.xyz
 * Requires at least: 5.1
 * Requires PHP: 7.3
 * Tested up to: 5.9
 * Text Domain: sdk-gf-encrypt
 */

require __DIR__ . '/vendor/autoload.php';

$plugin = new \Sanderdekroon\GFEncryption\Plugin();
$plugin->boot();
