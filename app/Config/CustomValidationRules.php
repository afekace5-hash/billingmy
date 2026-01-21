<?php

namespace Config;

class CustomValidationRules
{
    /**
     * Custom validation rule untuk IP address atau domain name
     */
    public function valid_ip_or_domain(?string $str = null): bool
    {
        if ($str === null || $str === '') {
            return false;
        }

        // Check if it's a valid IP address
        if (filter_var($str, FILTER_VALIDATE_IP)) {
            return true;
        }

        // Check if it contains port (for tunnel connections like "host:port")
        if (strpos($str, ':') !== false) {
            $parts = explode(':', $str);
            if (count($parts) === 2) {
                $host = $parts[0];
                $port = $parts[1];

                // Validate host part
                if (filter_var($host, FILTER_VALIDATE_IP) || $this->isValidDomain($host)) {
                    // Validate port part
                    return is_numeric($port) && $port > 0 && $port <= 65535;
                }
            }
            return false;
        }

        // Check if it's a valid domain name
        return $this->isValidDomain($str);
    }

    /**
     * Helper method to validate domain name
     */
    private function isValidDomain(string $domain): bool
    {
        // Basic domain validation
        return (bool) preg_match('/^[a-zA-Z0-9]([a-zA-Z0-9\-]{0,61}[a-zA-Z0-9])?(\.[a-zA-Z0-9]([a-zA-Z0-9\-]{0,61}[a-zA-Z0-9])?)*$/', $domain);
    }
}
