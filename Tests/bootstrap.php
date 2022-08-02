<?php

require_once dirname(__DIR__) . '/vendor/autoload.php';

if (!function_exists('esc_html__')) {
    function esc_html__( string $text, string $domain = 'default' ) {
        unset($domain);
        return $text;
    }
}
