<?php

/**
 * MikroTik Profile Helper Functions
 * 
 * Helper functions untuk mengelola profile MikroTik berdasarkan paket yang dipilih
 */

if (!function_exists('get_mikrotik_profile_for_package')) {
    /**
     * Get MikroTik profile name based on package ID
     * 
     * @param int $packageId Package ID
     * @return string Profile name or 'default' if not found
     */
    function get_mikrotik_profile_for_package($packageId)
    {
        if (!$packageId) {
            return 'default';
        }
        $paketModel = new \App\Models\PackageProfileModel();
        $package = $paketModel->find($packageId);

        if ($package && !empty($package->mikrotik_profile)) {
            return $package->mikrotik_profile;
        }        // Fallback: generate profile name based on bandwidth
        if ($package && !empty($package->bandwidth_profile)) {
            $bandwidth = strtolower($package->bandwidth_profile);
            // Remove 'mbps' and spaces, replace with profile format
            $bandwidth = str_replace(['mbps', ' ', 'm'], ['', '', ''], $bandwidth);
            return 'profile-' . $bandwidth . 'mbps';
        }

        return 'default';
    }
}

if (!function_exists('create_pppoe_secret_with_package_profile')) {
    /**
     * Create PPPoE secret with profile based on package
     * 
     * @param object $mikrotik MikroTik API instance
     * @param array $secretData Secret data
     * @param int $packageId Package ID
     * @return array Result of creation
     */
    function create_pppoe_secret_with_package_profile($mikrotik, $secretData, $packageId = null)
    {
        try {
            // Get appropriate profile
            $profile = get_mikrotik_profile_for_package($packageId);

            // Prepare parameters
            $params = [
                'name' => $secretData['username'],
                'password' => $secretData['password'],
                'service' => $secretData['service'] ?? 'pppoe',
                'profile' => $profile,
                'comment' => $secretData['comment'] ?? ''
            ];

            // Add optional parameters
            if (!empty($secretData['remote-address'])) {
                $params['remote-address'] = $secretData['remote-address'];
            }
            if (!empty($secretData['local-address'])) {
                $params['local-address'] = $secretData['local-address'];
            }
            if (isset($secretData['disabled'])) {
                $params['disabled'] = $secretData['disabled'] ? 'yes' : 'no';
            }

            // Create secret
            $result = $mikrotik->comm('/ppp/secret/add', $params);

            return [
                'success' => true,
                'profile_used' => $profile,
                'params' => $params,
                'result' => $result
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'profile_used' => $profile ?? 'default'
            ];
        }
    }
}

if (!function_exists('update_pppoe_secret_profile_by_package')) {
    /**
     * Update PPPoE secret profile based on package change
     * 
     * @param object $mikrotik MikroTik API instance
     * @param string $username PPPoE username
     * @param int $newPackageId New package ID
     * @return array Result of update
     */
    function update_pppoe_secret_profile_by_package($mikrotik, $username, $newPackageId)
    {
        try {
            // Find existing secret
            $secrets = $mikrotik->comm('/ppp/secret/print', ['?name' => $username]);

            if (empty($secrets)) {
                return [
                    'success' => false,
                    'error' => 'PPPoE secret not found'
                ];
            }

            $secret = $secrets[0];
            $secretId = $secret['.id'];
            $oldProfile = $secret['profile'] ?? 'default';

            // Get new profile
            $newProfile = get_mikrotik_profile_for_package($newPackageId);

            if ($oldProfile === $newProfile) {
                return [
                    'success' => true,
                    'message' => 'Profile already matches package',
                    'old_profile' => $oldProfile,
                    'new_profile' => $newProfile
                ];
            }

            // Update profile
            $result = $mikrotik->comm('/ppp/secret/set', [
                '.id' => $secretId,
                'profile' => $newProfile,
                'comment' => ($secret['comment'] ?? '') . ' - Profile updated: ' . date('Y-m-d H:i:s')
            ]);

            return [
                'success' => true,
                'message' => 'Profile updated successfully',
                'old_profile' => $oldProfile,
                'new_profile' => $newProfile,
                'result' => $result
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}

if (!function_exists('get_available_mikrotik_profiles')) {
    /**
     * Get available MikroTik profiles from router
     * 
     * @param object $mikrotik MikroTik API instance
     * @return array List of available profiles
     */
    function get_available_mikrotik_profiles($mikrotik)
    {
        try {
            $profiles = $mikrotik->comm('/ppp/profile/print');

            $profileList = [];
            foreach ($profiles as $profile) {
                $profileList[] = [
                    'name' => $profile['name'] ?? '',
                    'rate_limit' => $profile['rate-limit'] ?? '',
                    'local_address' => $profile['local-address'] ?? '',
                    'remote_address' => $profile['remote-address'] ?? '',
                    'comment' => $profile['comment'] ?? ''
                ];
            }

            return [
                'success' => true,
                'profiles' => $profileList
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'profiles' => []
            ];
        }
    }
}

if (!function_exists('validate_package_profile_mapping')) {
    /**
     * Validate if package profile exists in MikroTik
     * 
     * @param object $mikrotik MikroTik API instance
     * @param int $packageId Package ID
     * @return array Validation result
     */
    function validate_package_profile_mapping($mikrotik, $packageId)
    {
        try {
            $expectedProfile = get_mikrotik_profile_for_package($packageId);
            $availableProfiles = get_available_mikrotik_profiles($mikrotik);

            if (!$availableProfiles['success']) {
                return [
                    'success' => false,
                    'error' => 'Could not fetch available profiles'
                ];
            }

            $profileExists = false;
            foreach ($availableProfiles['profiles'] as $profile) {
                if ($profile['name'] === $expectedProfile) {
                    $profileExists = true;
                    break;
                }
            }

            return [
                'success' => true,
                'profile_exists' => $profileExists,
                'expected_profile' => $expectedProfile,
                'available_profiles' => array_column($availableProfiles['profiles'], 'name')
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}
