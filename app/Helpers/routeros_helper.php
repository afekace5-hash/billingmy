<?php

/**
 * RouterOS Helper Functions
 * 
 * Helper functions untuk RouterOS service
 */

if (!function_exists('routeros')) {
    /**
     * Get RouterOS service instance
     */
    function routeros()
    {
        return \Config\Services::routeros();
    }
}
