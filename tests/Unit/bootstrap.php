<?php

require dirname(dirname(__DIR__)) . '/vendor/autoload.php';

define('WP_PLUGIN_DIR', __DIR__);

if (! function_exists('rgar')) {
    function rgar($array, $prop, $default = null)
    {
        if (! is_array($array) && ! (is_object($array) && $array instanceof ArrayAccess)) {
            return $default;
        }

        if (isset($array[$prop])) {
            $value = $array[$prop];
        } else {
            $value = '';
        }

        return empty($value) && $default !== null ? $default : $value;
    }
}

/**
 * Bootstrap WordPress Mock.
 */
\WP_Mock::setUsePatchwork(true);
\WP_Mock::bootstrap();
