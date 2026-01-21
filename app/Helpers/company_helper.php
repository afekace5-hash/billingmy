<?php

if (!function_exists('getCompanyLogo')) {
    /**
     * Get company logo URL
     * 
     * @param string $size 'sm' for small logo, 'lg' for large logo
     * @return string Logo URL
     */
    function getCompanyLogo($size = 'lg')
    {
        static $company = null;

        // Cache company data to avoid multiple database queries
        if ($company === null) {
            try {
                $db = \Config\Database::connect();
                $query = $db->query("SELECT logo FROM companies ORDER BY id ASC LIMIT 1");
                $company = $query->getRowArray();
            } catch (\Exception $e) {
                log_message('error', 'Error fetching company logo: ' . $e->getMessage());
                $company = false;
            }
        }

        // Determine logo path
        $logoPath = WRITEPATH . '../public/uploads/' . $company['logo'];
        log_message('debug', 'Logo file path: ' . $logoPath . ' | Logo DB value: ' . ($company['logo'] ?? 'NULL'));
        if ($company && !empty($company['logo']) && file_exists($logoPath)) {
            return base_url('uploads/' . $company['logo']);
        }

        // Fallback to default logos
        return $size === 'sm'
            ? '' // No mini logo available
            : base_url('backend/assets/images/logo akanet.png');
    }
}

if (!function_exists('getCompanyData')) {
    /**
     * Get company data
     * 
     * @return array|null Company data
     */
    function getCompanyData()
    {
        try {
            $db = \Config\Database::connect();
            $query = $db->query("SELECT * FROM companies ORDER BY id ASC LIMIT 1");
            $company = $query->getRowArray();
        } catch (\Exception $e) {
            log_message('error', 'Error fetching company data: ' . $e->getMessage());
            $company = false;
        }
        return $company ?: null;
    }
}
