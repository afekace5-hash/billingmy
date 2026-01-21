<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\PackageProfileModel;

class LandingPage extends BaseController
{
    protected $packageProfileModel;

    public function __construct()
    {
        $this->packageProfileModel = new PackageProfileModel();
    }

    /**
     * Get active package profiles for landing page
     */
    public function getPackages()
    {
        try {
            $packages = $this->packageProfileModel
                ->where('status', 'active')
                ->orderBy('price', 'ASC')
                ->findAll();

            $data = [];
            foreach ($packages as $package) {
                $data[] = [
                    'id' => (int)$package['id'],
                    'name' => $package['name'] ?? '',
                    'description' => $package['description'] ?? '',
                    'price' => (int)($package['price'] ?? 0),
                    'validity_period' => (int)($package['validity_period'] ?? 30),
                    'bandwidth_profile' => $package['bandwidth_profile'] ?? '',
                    'group_profile' => $package['group_profile'] ?? '',
                ];
            }

            return $this->response->setJSON([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Landing page view for difihome
     */
    public function index()
    {
        return view('landing_page/difihome');
    }
}
