<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Services\MikroTikService;
use App\Models\GroupProfileModel;
use App\Models\LokasiServerModel;

class TestMikroTik extends BaseController
{
    public function syncTest()
    {
        try {
            // Get test profile and router
            $groupProfileModel = new GroupProfileModel();
            $lokasiServerModel = new LokasiServerModel();
            $mikrotikService = new MikroTikService();

            $testProfile = $groupProfileModel->find(7); // Test-Profile-2
            $router = $lokasiServerModel->find(12); // KIMONET router

            if (!$testProfile || !$router) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Test profile or router not found'
                ]);
            }

            echo "<h2>Testing MikroTik Sync</h2>";
            echo "<p>Profile: " . $testProfile['name'] . "</p>";
            echo "<p>Router: " . $router['name'] . " (" . $router['ip_router'] . ")</p>";

            echo "<h3>Starting sync...</h3>";

            // Test the sync
            $result = $mikrotikService->addGroupProfileToRouter($router, $testProfile);

            echo "<h3>Result:</h3>";
            echo "<p>Success: " . ($result['success'] ? 'YES' : 'NO') . "</p>";
            echo "<p>Message: " . htmlspecialchars($result['message']) . "</p>";

            if (isset($result['details'])) {
                echo "<h3>Details:</h3>";
                echo "<ul>";
                foreach ($result['details'] as $detail) {
                    echo "<li>" . htmlspecialchars($detail) . "</li>";
                }
                echo "</ul>";
            }

            echo "<h3>Check Log File:</h3>";
            echo "<p>Check writable/logs/log-" . date('Y-m-d') . ".log for detailed logging</p>";
        } catch (\Exception $e) {
            echo "<h2>Error:</h2>";
            echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
            echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
        }
    }
}
