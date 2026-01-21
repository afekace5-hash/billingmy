<?php

/*
 *---------------------------------------------------------------
 * AUTO CLEANUP TEST/DEBUG FILES
 *---------------------------------------------------------------
 */

// Auto cleanup test/debug/temp files on every run
$patterns = [
    __DIR__ . '/../test_*',
    __DIR__ . '/../debug_*',
    __DIR__ . '/../setup_*',
    __DIR__ . '/../verify_*',
    __DIR__ . '/../update_*',
    __DIR__ . '/../simple_*',
    __DIR__ . '/../prepare_*',
    __DIR__ . '/../preload.php',
    __DIR__ . '/../webhook_*',
    __DIR__ . '/../direct_*',
    __DIR__ . '/../list_tables.php',
    __DIR__ . '/../fix_*',
    __DIR__ . '/../demo_*',
    __DIR__ . '/../cron_*',
    __DIR__ . '/../check_*',
    __DIR__ . '/../create_*',
    __DIR__ . '/../cleanup_*',
    __DIR__ . '/../final_*',
    __DIR__ . '/../add_*',
    __DIR__ . '/../calculate_*',
    __DIR__ . '/../analyze_*'
];

foreach ($patterns as $pattern) {
    foreach (glob($pattern) as $file) {
        if (is_file($file)) {
            @unlink($file);
        }
    }
}

/*
 *---------------------------------------------------------------
 * CHECK PHP VERSION
 *---------------------------------------------------------------
 */

$minPhpVersion = '8.1'; // If you update this, don't forget to update `spark`.
if (version_compare(PHP_VERSION, $minPhpVersion, '<')) {
    $message = sprintf(
        'Your PHP version must be %s or higher to run CodeIgniter. Current version: %s',
        $minPhpVersion,
        PHP_VERSION
    );

    header('HTTP/1.1 503 Service Unavailable.', true, 503);
    echo $message;

    exit(1);
}

/*
 *---------------------------------------------------------------
 * SET THE CURRENT DIRECTORY
 *---------------------------------------------------------------
 */

// Path to the front controller (this file)
define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR);

// Ensure the current directory is pointing to the front controller's directory
if (getcwd() . DIRECTORY_SEPARATOR !== FCPATH) {
    chdir(FCPATH);
}

/*
 *---------------------------------------------------------------
 * BOOTSTRAP THE APPLICATION
 *---------------------------------------------------------------
 * This process sets up the path constants, loads and registers
 * our autoloader, along with Composer's, loads our constants
 * and fires up an environment-specific bootstrapping.
 */

// LOAD OUR PATHS CONFIG FILE
// This is the line that might need to be changed, depending on your folder structure.
require FCPATH . '../app/Config/Paths.php';
// ^^^ Change this line if you move your application folder

$paths = new Config\Paths();

// LOAD THE FRAMEWORK BOOTSTRAP FILE
require $paths->systemDirectory . '/Boot.php';

exit(CodeIgniter\Boot::bootWeb($paths));
