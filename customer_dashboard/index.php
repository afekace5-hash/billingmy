<?php

/**
 * Customer Dashboard Entry Point
 * Subdomain: customer-portal.domain.com
 */

// Define customer portal constant
define('CUSTOMER_PORTAL', true);

// Define root path (parent directory)
define('ROOTPATH', realpath(__DIR__ . '/../') . DIRECTORY_SEPARATOR);

// Load environment dari file terpisah
if (file_exists(__DIR__ . '/.env.customer')) {
    $envFile = __DIR__ . '/.env.customer';
} else {
    $envFile = ROOTPATH . '.env'; // Fallback ke root
}

// Load CodeIgniter paths
$pathsConfig = ROOTPATH . 'app/Config/Paths.php';
require realpath($pathsConfig) ?: $pathsConfig;

// Load CodeIgniter paths
$paths = new Config\Paths();

// Location of the framework bootstrap file
$bootstrap = rtrim($paths->systemDirectory, '\\/ ') . DIRECTORY_SEPARATOR . 'bootstrap.php';
require realpath($bootstrap) ?: $bootstrap;

// Load environment variables
require_once SYSTEMPATH . 'Config/DotEnv.php';
(new CodeIgniter\Config\DotEnv(ROOTPATH, $envFile))->load();

// Grab our CodeIgniter instance
$app = Config\Services::codeigniter();
$app->initialize();

// Override routes untuk customer portal only
$routes = service('routes');
$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('CustomerDashboard');
$routes->setDefaultMethod('index');

// Run!
$app->run();
