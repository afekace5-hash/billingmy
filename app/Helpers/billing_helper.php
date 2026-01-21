<?php

/**
 * Helper function untuk mendeteksi base URL berdasarkan domain
 * 
 * @return string
 */
if (!function_exists('get_dynamic_base_url')) {
    function get_dynamic_base_url(): string
    {
        $request = \Config\Services::request();
        $host = $request->getServer('HTTP_HOST');
        $scheme = $request->isSecure() ? 'https' : 'http';

        // Jika diakses dari domain billing, gunakan domain billing
        if ($host === 'bayarin.kimonet.my.id') {
            return $scheme . '://bayarin.kimonet.my.id/';
        }

        // Default fallback
        return $scheme . '://' . $host . '/';
    }
}

/**
 * Helper function untuk membuat billing link
 * 
 * @param string $serviceNumber
 * @return string
 */
if (!function_exists('get_billing_link')) {
    function get_billing_link(string $serviceNumber): string
    {
        return 'https://bayarin.kimonet.my.id/' . $serviceNumber;
    }
}
