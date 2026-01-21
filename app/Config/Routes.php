
<?php

// WhatsApp Broadcast AJAX
$routes->get('whatsapp/getCustomersByBranch', 'WhatsappMessage::getCustomersByBranch');

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// Remote Access routes (Geniacs)
$routes->group('remote-access', ['filter' => 'isLoggedIn'], function ($routes) {
    $routes->get('/', 'RemoteAccess::index');
    $routes->post('getData', 'RemoteAccess::getData');
    $routes->get('getCustomerDetail/(:num)', 'RemoteAccess::getCustomerDetail/$1');
    $routes->post('connectGeniacs', 'RemoteAccess::connectGeniacs');
    $routes->post('saveConfiguration', 'RemoteAccess::saveConfiguration');
    $routes->post('testConnection', 'RemoteAccess::testConnection');
});

// Ticket routes
$routes->get('ticket', 'Ticket::index');
$routes->get('ticket/create', 'Ticket::create');
$routes->post('ticket/store', 'Ticket::store');
$routes->get('ticket/(:num)', 'Ticket::show/$1');
$routes->post('ticket/(:num)/update', 'Ticket::update/$1');
$routes->post('ticket/(:num)/status', 'Ticket::updateStatus/$1');
$routes->post('ticket/(:num)/assign', 'Ticket::assign/$1');
$routes->delete('ticket/(:num)', 'Ticket::delete/$1');
$routes->post('ticket/(:num)/delete', 'Ticket::delete/$1');
$routes->get('ticket/filter', 'Ticket::filter');
$routes->post('ticket/data', 'Ticket::data');

// Prorate routes
$routes->group('prorate', ['filter' => 'isLoggedIn'], function ($routes) {
    $routes->get('/', 'Prorate::index');
    $routes->post('data', 'Prorate::data');
    $routes->get('get-customers', 'Prorate::getCustomers');
    $routes->post('save', 'Prorate::save');
    $routes->post('delete/(:num)', 'Prorate::delete/$1');
    $routes->post('auto-generate', 'Prorate::autoGenerate');
});

// Invoice routes (standalone, menggunakan InvoiceBesar untuk display, Invoices untuk generate)
$routes->get('invoice', 'InvoiceBesar::index', ['filter' => 'isLoggedIn']);
$routes->post('invoice/data', 'InvoiceBesar::data', ['filter' => 'isLoggedIn']);
$routes->get('invoice/get-widget-data', 'InvoiceBesar::getWidgetData', ['filter' => 'isLoggedIn']);
$routes->post('invoice/store', 'InvoiceBesar::store', ['filter' => 'isLoggedIn']);
$routes->post('invoice/delete/(:num)', 'InvoiceBesar::delete/$1', ['filter' => 'isLoggedIn']);
$routes->post('invoice/broadcast', 'InvoiceBesar::broadcast', ['filter' => 'isLoggedIn']);
$routes->get('invoice/export', 'InvoiceBesar::export', ['filter' => 'isLoggedIn']);
$routes->get('invoice/view/(:num)', 'InvoiceBesar::view/$1', ['filter' => 'isLoggedIn']);
$routes->post('invoice/generate', 'Invoices::generate', ['filter' => 'isLoggedIn']);

// Transaction routes
$routes->group('transaction', ['filter' => 'isLoggedIn'], function ($routes) {
    $routes->get('transaction', 'Transaction::transaction');
    $routes->post('data', 'Transaction::data');
    $routes->post('getSummary', 'Transaction::getSummary');
    $routes->post('store', 'Transaction::store');
    $routes->get('edit/(:num)', 'Transaction::edit/$1');
    $routes->post('update/(:num)', 'Transaction::update/$1');
    $routes->post('delete/(:num)', 'Transaction::delete/$1');
});

// Users routes
$routes->group('users', ['filter' => 'isLoggedIn'], function ($routes) {
    $routes->get('/', 'Users::index');
    $routes->post('data', 'Users::data');
    $routes->get('create', 'Users::create');
    $routes->post('store', 'Users::store');
    $routes->get('edit/(:num)', 'Users::edit/$1');
    $routes->post('update/(:num)', 'Users::update/$1');
    $routes->post('delete/(:num)', 'Users::delete/$1');
});

// Admin Promo Management Routes
$routes->group('admin/promos', ['filter' => 'isLoggedIn'], function ($routes) {
    $routes->get('/', 'PromoController::index');
    $routes->get('create', 'PromoController::create');
    $routes->post('store', 'PromoController::store');
    $routes->get('edit/(:num)', 'PromoController::edit/$1');
    $routes->post('update/(:num)', 'PromoController::update/$1');
    $routes->post('delete/(:num)', 'PromoController::delete/$1');
    $routes->post('toggle-active/(:num)', 'PromoController::toggleActive/$1');
});

// Customer Portal Routes - Subdomain Ready
// Login routes (public access)
$routes->group('customer-portal', function ($routes) {
    // Public routes (no auth required)
    $routes->get('/', 'CustomerDashboard::index');
    $routes->post('login', 'CustomerDashboard::login');
    $routes->get('check-session', 'CustomerDashboard::checkSession');
});

// Protected customer routes (require customer login)
$routes->group('customer-portal', ['filter' => 'customerAuth'], function ($routes) {
    $routes->get('dashboard', 'CustomerDashboard::dashboard');
    $routes->get('invoices', 'CustomerDashboard::invoices');
    $routes->get('profile', 'CustomerDashboard::profile');
    $routes->get('logout', 'CustomerDashboard::logout');
    $routes->post('pay-invoice', 'CustomerDashboard::payInvoice');
    $routes->post('process-payment', 'CustomerDashboard::processPayment');
    $routes->get('invoice-details/(:num)', 'CustomerDashboard::getInvoiceDetails/$1');
    $routes->get('get-invoice-detail/(:num)', 'CustomerDashboard::getInvoiceDetail/$1');
    $routes->get('download-invoice/(:num)', 'CustomerDashboard::downloadInvoice/$1');
    $routes->get('duitku-payment-methods', 'CustomerDashboard::getDuitkuPaymentMethods');

    // Development/testing routes
    $routes->get('test-duitku', 'CustomerDashboard::testDuitkuConnection');
});

// Login dan auth routes (harus di atas)
$routes->get('login', 'Auth::login');
$routes->post('auth/loginProcess', 'Auth::loginProcess');
$routes->get('auth/logout', 'Auth::logout');

// Dashboard route
$routes->get('/', 'Home::index', ['filter' => 'isLoggedIn']);
$routes->get('dashboard', 'Home::index', ['filter' => 'isLoggedIn']);

// Notification routes
$routes->get('notification', 'Notification::index', ['filter' => 'isLoggedIn']);
$routes->match(['GET', 'POST'], 'notification/get-data', 'Notification::getData', ['filter' => 'isLoggedIn']);
$routes->post('notification/mark-as-read/(:num)', 'Notification::markAsRead/$1', ['filter' => 'isLoggedIn']);
$routes->post('notification/delete/(:num)', 'Notification::delete/$1', ['filter' => 'isLoggedIn']);

// Installation routes
$routes->group('installation', ['filter' => 'isLoggedIn'], function ($routes) {
    $routes->get('waiting-list', 'Installation::waitingList');
    $routes->get('on-progress', 'Installation::onProgress');
    $routes->get('history', 'Installation::historyList');
    $routes->get('history/(:num)', 'Installation::historyDetail/$1');
    $routes->post('process/(:num)', 'Installation::processInstallation/$1');
    $routes->post('cancel/(:num)', 'Installation::cancelWaitinglist/$1');
    $routes->post('cancel-progress/(:num)', 'Installation::cancelProgress/$1');
    $routes->post('activate/(:num)', 'Installation::activateInstallation/$1');
    $routes->get('get-customer-detail/(:num)', 'Installation::getCustomerDetail/$1');
});

// Debug route for isolir (temporary - no auth required)
$routes->get('debug/isolir', function () {
    // Get router list for dropdown
    $routerModel = new \App\Models\RouterOSModel();
    $routers = $routerModel->findAll();

    // Get customer data with PPPoE info
    $customerModel = new \App\Models\CustomerModel();
    $customers = $customerModel->select('id_customers, nama_pelanggan, pppoe_username, id_lokasi_server, status_tagihan, isolir_status, isolir_date, isolir_reason')
        ->where('pppoe_username IS NOT NULL')
        ->where('pppoe_username !=', '')
        ->findAll();

    $debug = [
        'total_customers' => count($customers),
        'total_routers' => count($routers),
        'customers_sample' => array_slice($customers, 0, 5),
        'routers_sample' => array_slice($routers, 0, 3),
        'isolated_count' => count(array_filter($customers, function ($c) {
            return $c['isolir_status'] == 1;
        }))
    ];

    return service('response')->setJSON([
        'success' => true,
        'debug' => $debug,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
});

// Public system info endpoint (no auth required) for basic system monitoring
$routes->get('api/public/system-info', 'Api\Dashboard::publicSystemInfo');

$routes->setAutoRoute(true);
$routes->get('dashboard/system-info', 'Home::systemInfo', ['filter' => 'isLoggedIn']);
$routes->post('dashboard/refresh-system-info', 'Home::refreshSystemInfo', ['filter' => 'isLoggedIn']);
$routes->get('dashboard/test-system-info', 'Home::testSystemInfo', ['filter' => 'isLoggedIn']);

// Hapus redirect yang bertentangan

// $routes->get('customers', 'Customer::index');
$routes->get('customer/paketOptions', 'Customer::paketOptions', ['filter' => 'isLoggedIn']);
$routes->get('customer/branchOptions', 'Customer::branchOptions', ['filter' => 'isLoggedIn']);
$routes->get('customer/areaOptions', 'Customer::areaOptions', ['filter' => 'isLoggedIn']);
$routes->get('customer/getCustomerOptions', 'Customer::getCustomerOptions', ['filter' => 'isLoggedIn']);
$routes->post('customer/searchPPPSecrets', 'Customer::searchPPPSecrets', ['filter' => 'isLoggedIn']);

$routes->post('customer/getUsedIPs', 'Customer::getUsedIPs', ['filter' => 'isLoggedIn']);
$routes->post('customer/getAvailableIPs', 'Customer::getAvailableIPs', ['filter' => 'isLoggedIn']);
$routes->post('customer/getNextAvailableIP', 'Customer::getNextAvailableIP', ['filter' => 'isLoggedIn']);
$routes->post('customer/calculatePricingSchema', 'Customer::calculatePricingSchema', ['filter' => 'isLoggedIn']);
$routes->get('customer/getGroupProfiles', 'Customer::getGroupProfiles', ['filter' => 'isLoggedIn']);
$routes->get('customer/debug-mikrotik', 'Customer::debugMikrotikConnection', ['filter' => 'isLoggedIn']);
$routes->get('customer/debugGroupProfiles', 'Customer::debugGroupProfiles', ['filter' => 'isLoggedIn']);

// PPPoE MikroTik routes
$routes->post('customer/savePppoeToMikrotik', 'Customer::savePppoeToMikrotik', ['filter' => 'isLoggedIn']);
$routes->get('customer/debug-mikrotik-pppoe', 'Customer::debugMikrotikPppoe', ['filter' => 'isLoggedIn']);
$routes->post('customer/debugPppoeCreation', 'Customer::debugPppoeCreation', ['filter' => 'isLoggedIn']);
$routes->post('customer/getPppoeAvailabilityStatus', 'Customer::getPppoeAvailabilityStatus', ['filter' => 'isLoggedIn']);
$routes->post('customer/syncPppoeSecret/(:num)', 'Customer::syncPppoeSecret/$1', ['filter' => 'isLoggedIn']);

// Customer Maps routes
$routes->get('customers/map-customers', 'Customer::showMaps', ['filter' => 'isLoggedIn']);

// Arus Kas routes
$routes->group('arus_kas', ['filter' => 'isLoggedIn'], function ($routes) {
    // Main views
    $routes->get('/', 'ArusKas::index');
    $routes->get('category', 'ArusKas::category');
    $routes->get('cash-flow', 'ArusKas::cashFlow');

    // DataTables endpoints
    $routes->get('data', 'ArusKas::data'); // for cash flow data
    $routes->get('categoryList', 'ArusKas::categoryList'); // for category list

    // Category management endpoints
    $routes->get('categoryEdit/(:num)', 'ArusKas::categoryEdit/$1');
    $routes->post('categorySave', 'ArusKas::categorySave');
    $routes->delete('categoryDelete/(:num)', 'ArusKas::categoryDelete/$1');

    // Cash flow management endpoints
    $routes->get('getFlow/(:num)', 'ArusKas::getFlow/$1');
    $routes->post('flowSave', 'ArusKas::flowSave');
    $routes->post('delete/(:num)', 'ArusKas::flowDelete/$1'); // Changed from DELETE to POST
    $routes->post('deleteAll', 'ArusKas::deleteAll'); // Changed from DELETE to POST

    // Widget data endpoint
    $routes->get('get-widget-cash-data', 'ArusKas::getWidgetCashData');
});

// Biaya Tambahan routes
$routes->group('biaya_tambahan', ['filter' => 'isLoggedIn'], function ($routes) {
    // Main views
    $routes->get('/', 'BiayaTambahan::index');

    // DataTables endpoints
    $routes->get('data', 'BiayaTambahan::data');
    $routes->get('list', 'BiayaTambahan::list');

    // CRUD endpoints
    $routes->post('create', 'BiayaTambahan::create');
    $routes->get('edit/(:num)', 'BiayaTambahan::edit/$1');
    $routes->post('update/(:num)', 'BiayaTambahan::update/$1');
    $routes->post('delete/(:num)', 'BiayaTambahan::delete/$1');
});

// MikroTik Profile Management Routes
$routes->group('mikrotik-profiles', ['filter' => 'isLoggedIn'], function ($routes) {
    $routes->get('load', 'MikroTikProfile::load');
    $routes->post('sync', 'MikroTikProfile::sync');
});

// API routes for MikroTik
$routes->group('api/mikrotik', function ($routes) {
    $routes->get('pppoe-profiles', 'MikroTikProfile::getPppoeProfiles');
    $routes->get('test-connection', 'MikroTikTest::testConnection');
});

// API routes for package profiles
$routes->group('api', function ($routes) {
    $routes->post('package-profiles/datatable', 'InternetPackages::getPackageProfileData');
});

// Invoice periods API
$routes->get('invoices/available-periods', 'Invoices::getAvailablePeriods', ['filter' => 'isLoggedIn']);

// Note: internet-packages routes are defined in a separate group below
// Clustering routes are defined in a group below instead of using resource routes
$routes->resource('customers', [
    'filter' => 'isLoggedIn',
    'controller' => 'Customer',
]);
$routes->get('customers/new', 'Customer::new', ['filter' => 'isLoggedIn']);
$routes->post('customers/new', 'Customer::create', ['filter' => 'isLoggedIn']);
$routes->get('customers/location-names/(:num)', 'Customer::getLocationNames/$1', ['filter' => 'isLoggedIn']); // AJAX endpoint for location names
$routes->get('customers/test-lokasi', 'Customer::testLokasiServer', ['filter' => 'isLoggedIn']); // Test endpoint

// Customer Import/Export routes
$routes->post('customers/import', 'Customer::import', ['filter' => 'isLoggedIn']);
$routes->get('customers/import/download-example', 'Customer::downloadExample', ['filter' => 'isLoggedIn']);
$routes->get('customers/export/excel', 'Customer::exportExcel', ['filter' => 'isLoggedIn']);

// Regional data routes for dropdowns - Using existing WilayahProxy
$routes->get('customers/get-cities/(:segment)', 'WilayahProxy::regencies/$1', ['filter' => 'isLoggedIn']);
$routes->get('customers/get-districts/(:segment)', 'WilayahProxy::districts/$1', ['filter' => 'isLoggedIn']);
$routes->get('customers/get-villages/(:segment)', 'WilayahProxy::villages/$1', ['filter' => 'isLoggedIn']);

// Customer Map route
$routes->get('customers/map-customers', 'Customer::mapCustomers', ['filter' => 'isLoggedIn']);
// Test route
$routes->get('test-map', 'Customer::testMapRoute');

// Customer Statistics routes
$routes->get('customers/monthly-stats-page', 'Customer::monthlyStatsPage', ['filter' => 'isLoggedIn']);
$routes->get('customers/monthly-stats', 'Customer::monthlyStats', ['filter' => 'isLoggedIn']);
$routes->get('customers/recent-stats', 'Customer::recentStats', ['filter' => 'isLoggedIn']);
$routes->get('system/test', 'SystemTest::index', ['filter' => 'isLoggedIn']); // System integration test

// Router auto isolir
// System Update Logs AJAX Routes
$routes->post('system/update-logs/data', 'SystemUpdate::getLogsData', ['filter' => 'isLoggedIn']);
$routes->get('system/update-logs/export', 'SystemUpdate::exportLogs', ['filter' => 'isLoggedIn']);
$routes->post('system/update-logs/clear', 'SystemUpdate::clearOldLogs', ['filter' => 'isLoggedIn']);
// Git Management Routes
$routes->post('system/git/initialize', 'SystemUpdate::initializeGitRepository', ['filter' => 'isLoggedIn']);
$routes->post('system/git/set-remote', 'SystemUpdate::setGitRemote', ['filter' => 'isLoggedIn']);
$routes->post('customer/testMikrotikConnection', 'Customer::testMikrotikConnection', ['filter' => 'isLoggedIn']); // MikroTik connection test
$routes->post('customers/searchPpp', 'Customer::searchPpp', ['filter' => 'isLoggedIn']); // PPP secret search
$routes->post('customers/sync-pppoe/(:num)', 'Customer::syncPppoeSecret/$1', ['filter' => 'isLoggedIn']); // Manual PPPoE sync
// RADIUS routes removed - Using MikroTik PPP Secrets only
$routes->post('clustering/store', 'Cluster::store', ['filter' => 'isLoggedIn']);

// PPPoE Account Management
$routes->post('pppoe/create/(:num)', 'PppoeAccounts::createAccount/$1', ['filter' => 'isLoggedIn']);

$routes->post('customers/data', 'Customer::data', ['filter' => 'isLoggedIn']);
$routes->post('customers/delete', 'Customer::deleteSelected', ['filter' => 'isLoggedIn']); // Bulk delete customers

$routes->get('payment', 'Payment::index', ['filter' => 'isLoggedIn']);
$routes->get('payment/demo', 'Payment::demo', ['filter' => 'isLoggedIn']);
$routes->get('payment/setup-guide', 'Payment::setupGuide', ['filter' => 'isLoggedIn']);
$routes->post('payment/createInvoice', 'Payment::createInvoice', ['filter' => 'isLoggedIn']);
$routes->get('payment/get-active-gateways', 'Payment::getActiveGateways', ['filter' => 'isLoggedIn']);
$routes->post('payment/set-method', 'Payment::setMethod', ['filter' => 'isLoggedIn']);
$routes->get('payment/process/(:num)', 'Payment::process/$1');

// Payment callback routes - prioritize specific routes first
$routes->match(['GET', 'POST'], 'payment/callback/midtrans', 'PaymentCallback::handleCallback/midtrans');
$routes->match(['GET', 'POST'], 'payment/callback/flip', 'PaymentCallback::handleCallback/flip');
$routes->post('payment/callback', 'PaymentCallback::handleCallback');
$routes->get('payment/callback', 'PaymentCallback::handleCallback');
$routes->post('payment/callback/(:segment)', 'PaymentCallback::handleCallback/$1');
$routes->get('payment/callback/(:segment)', 'PaymentCallback::handleCallback/$1');
$routes->post('payment/test-connection', 'Payment::testConnection', ['filter' => 'isLoggedIn']);
$routes->get('payment/methods', 'Payment::methods', ['filter' => 'isLoggedIn']);
$routes->get('payment/gateway-status', 'Payment::gatewayStatus', ['filter' => 'isLoggedIn']);
$routes->get('payment/debug-gateways', 'Payment::debugGateways', ['filter' => 'isLoggedIn']);


// Quick Setup Routes
$routes->get('quick-setup/check-system', 'QuickSetup::checkSystem', ['filter' => 'isLoggedIn']);

// ============================================================================
// CUSTOMER MOBILE APP API ROUTES
// ============================================================================

// Authentication API (Public - No authentication required)
$routes->group('api/auth', function ($routes) {
    $routes->post('login', 'Api\AuthController::login');
    $routes->post('set-password', 'Api\AuthController::setPassword');
    $routes->post('forgot-password', 'Api\AuthController::forgotPassword');
    $routes->post('reset-password', 'Api\AuthController::resetPassword');

    // Protected routes (requires authentication)
    $routes->post('logout', 'Api\AuthController::logout', ['filter' => 'apiAuth']);
    $routes->post('change-password', 'Api\AuthController::changePassword', ['filter' => 'apiAuth']);
    $routes->post('register-fcm', 'Api\AuthController::registerFCM', ['filter' => 'apiAuth']);
});

// Customer Portal API (Protected - Requires authentication)
$routes->group('api/customer', ['filter' => 'apiAuth'], function ($routes) {
    $routes->get('profile', 'Api\CustomerController::profile');
    $routes->put('profile', 'Api\CustomerController::updateProfile');
    $routes->get('status', 'Api\CustomerController::status');
    $routes->get('invoices', 'Api\CustomerController::invoices');
    $routes->get('invoice/(:num)', 'Api\CustomerController::invoiceDetail/$1');
    $routes->get('payment-history', 'Api\CustomerController::paymentHistory');
});

// Payment API (Protected - Requires authentication)
$routes->group('api/payment', ['filter' => 'apiAuth'], function ($routes) {
    $routes->get('methods', 'Api\PaymentController::methods');
    $routes->post('create', 'Api\PaymentController::create');
    $routes->get('status/(:any)', 'Api\PaymentController::status/$1');
    $routes->post('cancel/(:any)', 'Api\PaymentController::cancel/$1');
});

// Notification API (Protected - Requires authentication)
$routes->group('api/notifications', ['filter' => 'apiAuth'], function ($routes) {
    $routes->get('/', 'Api\NotificationController::index');
    $routes->get('unread-count', 'Api\NotificationController::unreadCount');
    $routes->put('(:num)/read', 'Api\NotificationController::markAsRead/$1');
    $routes->put('read-all', 'Api\NotificationController::markAllAsRead');
    $routes->delete('(:num)', 'Api\NotificationController::delete/$1');
    $routes->post('register-fcm', 'Api\NotificationController::registerFCM');
    $routes->post('test', 'Api\NotificationController::testNotification'); // For testing only
});

// ============================================================================
// WEB ADMIN API ROUTES (Existing)
// ============================================================================

// API Routes
$routes->get('api/payment-methods', 'Api\PaymentMethods::index', ['filter' => 'isLoggedIn']);
$routes->post('api/payment-methods', 'Api\PaymentMethods::index');
$routes->get('api/payment-fees', 'Api\PaymentFees::index', ['filter' => 'isLoggedIn']);
$routes->post('api/payment-fees', 'Api\PaymentFees::index');
$routes->get('api/invoice-details/(:num)', 'Api\InvoiceDetails::show/$1', ['filter' => 'isLoggedIn']);
$routes->post('api/invoice-details/(:num)', 'Api\InvoiceDetails::show/$1', ['filter' => 'isLoggedIn']);

// Landing Page API Routes (public, no authentication required)
$routes->post('api/landing/register', 'LandingPageApi::register');
$routes->get('api/landing/info', 'LandingPageApi::info');
$routes->get('api/landing/packages', 'LandingPage::getPackages');

// Wilayah API Proxy Routes (for Indonesian regional data)
$routes->get('api/wilayah/provinces', 'WilayahProxy::provinces');
$routes->get('api/wilayah/regencies/(:num)', 'WilayahProxy::regencies/$1');
$routes->get('api/wilayah/districts/(:num)', 'WilayahProxy::districts/$1');
$routes->get('api/wilayah/villages/(:num)', 'WilayahProxy::villages/$1');
$routes->post('api/wilayah/clear-cache', 'WilayahProxy::clearCache', ['filter' => 'isLoggedIn']);

// Traffic Monitoring API Routes
$routes->get('api/dashboard/customer-stats', 'Api\Dashboard::customerStats', ['filter' => 'isLoggedIn']);
$routes->get('api/dashboard/customer-stats-optimized', 'Api\DashboardOptimized::customerStats', ['filter' => 'isLoggedIn']);
$routes->get('api/dashboard/payment-stats', 'Api\Dashboard::paymentStats', ['filter' => 'isLoggedIn']);
$routes->get('api/dashboard/financial-chart', 'Api\Dashboard::financialChart', ['filter' => 'isLoggedIn']);
$routes->get('api/system/info', 'Api\Dashboard::systemInfo', ['filter' => 'isLoggedIn']);
$routes->get('api/dashboard/system-info', 'Api\Dashboard::systemInfo', ['filter' => 'isLoggedIn']);
$routes->get('api/dashboard/real-system-info', 'Api\Dashboard::realSystemInfo', ['filter' => 'isLoggedIn']);
$routes->get('api/traffic/servers', 'Api\TrafficMonitor::servers', ['filter' => 'isLoggedIn']);
$routes->get('api/traffic/interfaces', 'Api\TrafficMonitor::interfaces', ['filter' => 'isLoggedIn']);
$routes->get('api/traffic/interfaces/(:segment)', 'Api\TrafficMonitor::interfaces/$1', ['filter' => 'isLoggedIn']);
$routes->get('api/traffic/stats/(:segment)/(:segment)', 'Api\TrafficMonitor::stats/$1/$2', ['filter' => 'isLoggedIn']);
$routes->get('api/traffic/data/(:segment)/(:segment)', 'Api\TrafficMonitor::traffic/$1/$2', ['filter' => 'isLoggedIn']);

// Public Billing Routes
$routes->get('cek-tagihan', 'PublicBilling::index');
$routes->get('public-billing', 'PublicBilling::index');
$routes->get('public-billing/check-bill', 'PublicBilling::checkBill');
$routes->post('public-billing/pay-invoice', 'PublicBilling::payInvoice');
$routes->get('billing/check/(:segment)', 'PublicBilling::checkBill/$1');
$routes->post('billing/pay', 'PublicBilling::payInvoice');

// Check payment status (for manual verification in localhost/sandbox)
$routes->get('check-payment-status/(:segment)', 'CheckPaymentStatus::checkStatus/$1');

$routes->get('router-os-conf', 'RouterOSConf::index', ['filter' => 'isLoggedIn']);
$routes->post('router-os-conf/data', 'RouterOSConf::getData', ['filter' => 'isLoggedIn']);
$routes->get('router-os-conf/status', 'RouterOSConf::status', ['filter' => 'isLoggedIn']); // DataTables endpoint
$routes->get('router-os-conf/realtime-status', 'RouterOSConf::getRealtimeStatus', ['filter' => 'isLoggedIn']); // Real-time status
$routes->post('router-os-conf/ping-check/(:num)', 'RouterOSConf::pingCheck/$1', ['filter' => 'isLoggedIn']); // Manual ping check
$routes->post('router-os-conf/toggle-auto-ping/(:num)', 'RouterOSConf::toggleAutoPing/$1', ['filter' => 'isLoggedIn']); // Toggle auto ping
$routes->get('router-os-conf/(:num)', 'RouterOSConf::view/$1', ['filter' => 'isLoggedIn']); // View detail page
$routes->get('router-os-conf/(:num)/edit', 'RouterOSConf::edit/$1', ['filter' => 'isLoggedIn']);
$routes->post('router-os-conf/check-connection', 'RouterOSConf::checkConnection', ['filter' => 'isLoggedIn']);
$routes->post('router-os-conf/sync/(:num)', 'RouterOSConf::sync/$1', ['filter' => 'isLoggedIn']); // Sync route
$routes->post('apiproxy/checkmikrotikconnection', 'ApiProxy::checkMikrotikConnection', ['filter' => 'isLoggedIn']);
$routes->post('router-os-conf', 'RouterOSConf::create', ['filter' => 'isLoggedIn']);
$routes->post('router-os-conf/(:num)', 'RouterOSConf::update/$1', ['filter' => 'isLoggedIn']); // Update route for PUT via POST
$routes->delete('router-os-conf/(:num)', 'RouterOSConf::delete/$1', ['filter' => 'isLoggedIn']); // Delete route
// Route for AJAX lokasi server select2
$routes->get('lokasi-server/options', 'LokasiServer::options', ['filter' => 'isLoggedIn']);
$routes->post('lokasi-server/update/(:num)', 'LokasiServer::update/$1');

// Transaction group routes
$routes->group('transaction', ['filter' => 'isLoggedIn'], function ($routes) {
    $routes->get('invoices', 'Invoices::index');
    $routes->get('invoices/test', 'Invoices::test');
    $routes->post('invoices/get/data', 'Invoices::getData');
    $routes->post('invoices/process-payment-button', 'Invoices::processPaymentButton');
    $routes->get('invoices/widget/get-widget-invoice', 'Invoices::getWidgetInvoice');
    $routes->get('invoices/widget/get-difference-invoice/(:any)', 'Invoices::getDifferenceInvoice/$1');
    $routes->delete('invoices/(:num)', 'Invoices::delete/$1');
    $routes->get('invoices/(:num)', 'Invoices::show/$1');
    $routes->get('invoices/proxy-history', 'Invoices::proxyHistory');
    $routes->match(['GET', 'POST'], 'invoices/get/history/(:segment)', 'Invoices::getHistory/$1');
    $routes->post('invoices/generate', 'Invoices::generate');
    $routes->post('invoices/generate-prorates', 'Invoices::generateProrates');
    $routes->post('invoices/generate-bill-single', 'Invoices::generateBillSingle');
    $routes->post('invoices/payment-confirmation', 'Invoices::paymentConfirmation');
    $routes->post('invoices/resend-whatsapp/(:num)', 'Invoices::resendWhatsApp/$1');
    $routes->get('invoices/view/(:num)', 'Invoices::view/$1');
    $routes->get('invoices/download-pdf/(:num)', 'Invoices::downloadPdf/$1');
    $routes->get('invoices/download-thermal/(:num)', 'Invoices::downloadThermal/$1');
    $routes->get('invoices/print/(:segment)', 'Invoices::print/$1');
    $routes->post('invoices/multi-payment', 'Invoices::multiPayment');
    $routes->post('invoices/delete-all', 'Invoices::deleteAll');
    $routes->post('invoices/get-unpaid-total', 'Invoices::getUnpaidTotal');
    $routes->post('invoices/search-customers', 'Invoices::searchCustomers');
    $routes->post('invoices/get-by-customer', 'Invoices::getInvoicesByCustomer');
    $routes->post('invoices/manual-paid', 'Invoices::manualPaid');
    $routes->post('invoices/get-detail', 'Invoices::getDetail');
    $routes->get('invoices/available-periods', 'Invoices::getAvailablePeriods');
    $routes->post('invoices/import', 'Invoices::import');
    $routes->get('invoices/import/download-example', 'Invoices::downloadImportExample');
    $routes->get('invoices/whatsapp/sendBillPaid/(:segment)', 'Invoices::sendBillPaid/$1');
    $routes->get('invoices/whatsapp/sendBillReminder/(:segment)', 'Invoices::sendBillReminder/$1');
});

// Router management routes
$routes->get('routers', 'Routers::index', ['filter' => 'isLoggedIn']);
$routes->get('routers/list', 'Routers::list', ['filter' => 'isLoggedIn']);
$routes->get('routers/ping', 'Routers::ping', ['filter' => 'isLoggedIn']);
$routes->post('routers/store', 'Routers::store', ['filter' => 'isLoggedIn']);
$routes->post('routers/check-connection', 'Routers::checkConnection', ['filter' => 'isLoggedIn']);
$routes->post('routers/syncronize', 'Routers::syncronize', ['filter' => 'isLoggedIn']);
$routes->get('routers/json', 'Routers::syncronize', ['filter' => 'isLoggedIn']);
$routes->get('routers/data', 'Routers::getData', ['filter' => 'isLoggedIn']);
$routes->get('routers/(:num)/mikrotik-info', 'Routers::mikrotikInfo/$1', ['filter' => 'isLoggedIn']);
$routes->get('routers/(:num)/system-resources', 'Routers::systemResources/$1', ['filter' => 'isLoggedIn']);
$routes->post('routers/(:num)/ping-mikrotik', 'Routers::pingMikrotik/$1', ['filter' => 'isLoggedIn']);
$routes->get('routers/(:num)/pppoe-active-count', 'Routers::pppoeActiveCount/$1', ['filter' => 'isLoggedIn']);
$routes->get('routers/(:num)/edit', 'Routers::edit/$1', ['filter' => 'isLoggedIn']);
$routes->put('routers/(:num)/update', 'Routers::update/$1', ['filter' => 'isLoggedIn']);
$routes->delete('routers/(:num)/delete', 'Routers::delete/$1', ['filter' => 'isLoggedIn']);
$routes->get('routers/(:num)/pppoe-binding-stats', 'Routers::pppoeBindingStats/$1', ['filter' => 'isLoggedIn']);
// PPPoE CRUD routes (dengan authentication filter)
$routes->get('routers/(:num)/pppoe/(:segment)', 'Routers::getPppoe/$1/$2', ['filter' => 'isLoggedIn']);
$routes->post('routers/(:num)/pppoe', 'Routers::createPppoe/$1', ['filter' => 'isLoggedIn']);
$routes->put('routers/(:num)/pppoe/(:segment)', 'Routers::updatePppoe/$1/$2', ['filter' => 'isLoggedIn']);
$routes->delete('routers/(:num)/pppoe/(:segment)', 'Routers::deletePppoe/$1/$2', ['filter' => 'isLoggedIn']);
// PPPoE list removed - VPN tunnel too slow for real-time data

// PPPoE Accounts Management
$routes->get('pppoe-accounts', 'PppoeAccounts::index', ['filter' => 'isLoggedIn']);
$routes->get('pppoe-accounts/create', 'PppoeAccounts::create', ['filter' => 'isLoggedIn']);
$routes->get('pppoe-accounts/get-customers', 'PppoeAccounts::getCustomers', ['filter' => 'isLoggedIn']);
$routes->post('pppoe-accounts/get-data', 'PppoeAccounts::getData', ['filter' => 'isLoggedIn']);
$routes->post('pppoe-accounts/store', 'PppoeAccounts::store', ['filter' => 'isLoggedIn']);
$routes->post('pppoe-accounts/sync', 'PppoeAccounts::sync', ['filter' => 'isLoggedIn']);
$routes->post('pppoe-accounts/delete/(:num)', 'PppoeAccounts::delete/$1', ['filter' => 'isLoggedIn']);
$routes->get('pppoe-accounts/export', 'PppoeAccounts::export', ['filter' => 'isLoggedIn']);

// Isolir Management Routes (dengan authentication filter)
$routes->get('routers/isolir', 'Routers::isolir', ['filter' => 'isLoggedIn']);
$routes->post('routers/isolir/execute', 'Routers::executeIsolir', ['filter' => 'isLoggedIn']);
$routes->post('routers/isolir/bulk-execute', 'Routers::bulkExecuteIsolir', ['filter' => 'isLoggedIn']);
$routes->get('routers/isolir/log', 'Routers::isolirLog', ['filter' => 'isLoggedIn']);

// Auto Isolir routes (dengan authentication filter)
$routes->get('routers/auto-isolir-config', 'Routers::autoIsolirConfig', ['filter' => 'isLoggedIn']);
$routes->post('routers/auto-isolir-config/save', 'Routers::saveAutoIsolirConfig', ['filter' => 'isLoggedIn']);
$routes->post('routers/auto-isolir-config/delete/(:num)', 'Routers::deleteAutoIsolirConfig/$1', ['filter' => 'isLoggedIn']);
$routes->post('routers/auto-isolir-config/setup-profiles', 'Routers::setupIsolirProfiles', ['filter' => 'isLoggedIn']);
$routes->post('routers/auto-isolir-config/verify-setup', 'Routers::verifyIsolirSetup', ['filter' => 'isLoggedIn']);
$routes->post('routers/auto-isolir/run', 'Routers::runAutoIsolir', ['filter' => 'isLoggedIn']);
$routes->get('routers/auto-isolir/preview', 'Routers::previewAutoIsolir', ['filter' => 'isLoggedIn']);

// Additional Auto Isolir routes for AJAX calls
$routes->get('routers/autoIsolirPreview', 'Routers::autoIsolirPreview', ['filter' => 'isLoggedIn']);
$routes->post('routers/autoIsolirRun', 'Routers::autoIsolirRun', ['filter' => 'isLoggedIn']);
$routes->get('routers/getAutoIsolirConfig/(:num)', 'Routers::getAutoIsolirConfig/$1', ['filter' => 'isLoggedIn']);
$routes->post('routers/addAutoIsolirConfig', 'Routers::saveAutoIsolirConfig', ['filter' => 'isLoggedIn']);
$routes->get('routers/editAutoIsolirConfig/(:num)', 'Routers::editAutoIsolirConfig/$1', ['filter' => 'isLoggedIn']);
$routes->post('routers/updateAutoIsolirConfig', 'Routers::updateAutoIsolirConfig', ['filter' => 'isLoggedIn']);
$routes->delete('routers/deleteAutoIsolirConfig/(:num)', 'Routers::deleteAutoIsolirConfig/$1', ['filter' => 'isLoggedIn']);

// Router diagnostic routes (dengan authentication filter)
$routes->get('diagnostic', 'RouterDiagnostic::index', ['filter' => 'isLoggedIn']);
$routes->post('diagnostic/test-connectivity', 'RouterDiagnostic::testConnectivity', ['filter' => 'isLoggedIn']);

$routes->get('settings/company', 'Settings::company', ['filter' => 'isLoggedIn']);
$routes->post('companies', 'Settings::saveCompany', ['filter' => 'isLoggedIn']);
$routes->post('settings/company/save', 'Settings::saveCompany', ['filter' => 'isLoggedIn']); // Alternative route
$routes->get('settings/company/test', function () {
    return 'Route test works!';
}); // Debug route
$routes->get('settings/company/debug', 'Settings::debugRoute'); // Debug controller
$routes->get('settings/applications', 'Settings::applications', ['filter' => 'isLoggedIn']);
$routes->post('settings/applications/store', 'Settings::storeApplications', ['filter' => 'isLoggedIn']);
$routes->get('settings/applications/getEdit', 'Settings::getEdit', ['filter' => 'isLoggedIn']);

$routes->get('settings/master-bank', 'MasterBank::index', ['filter' => 'isLoggedIn']);
$routes->post('settings/master-bank/create', 'MasterBank::create', ['filter' => 'isLoggedIn']);
$routes->get('settings/master-bank/delete/(:num)', 'MasterBank::delete/$1', ['filter' => 'isLoggedIn']);
$routes->get('settings/master-bank/edit/(:num)', 'MasterBank::edit/$1', ['filter' => 'isLoggedIn']);
$routes->post('settings/master-bank/update/(:num)', 'MasterBank::update/$1', ['filter' => 'isLoggedIn']);

// Branch Management Routes
$routes->get('settings/branch', 'Settings::branch', ['filter' => 'isLoggedIn']);
$routes->post('settings/branch/list', 'Settings::branchList', ['filter' => 'isLoggedIn']);
$routes->post('settings/branch/store', 'Settings::branchStore', ['filter' => 'isLoggedIn']);
$routes->get('settings/branch/edit/(:num)', 'Settings::branchEdit/$1', ['filter' => 'isLoggedIn']);
$routes->post('settings/branch/update', 'Settings::branchUpdate', ['filter' => 'isLoggedIn']);
$routes->post('settings/branch/delete/(:num)', 'Settings::branchDelete/$1', ['filter' => 'isLoggedIn']);

$routes->get('settings/payment-getway', 'Settings::paymentGateway');
$routes->post('settings/payment-getway', 'Settings::savePaymentGateway');
$routes->get('settings/payment-fees', 'PaymentFeesSettings::index', ['filter' => 'isLoggedIn']);
$routes->post('settings/payment-fees/update', 'PaymentFeesSettings::update', ['filter' => 'isLoggedIn']);
$routes->get('settings/payment-fees/gateway/(:segment)', 'PaymentFeesSettings::getGatewayFees/$1', ['filter' => 'isLoggedIn']);
$routes->get('master-bank/list-json', 'MasterBank::listJson');

// Geniacs Parameter Routes
$routes->group('addons', ['filter' => 'isLoggedIn'], function ($routes) {
    // Geniacs routes
    $routes->get('geniacs', 'Addons::geniacs');
    $routes->get('getParameters', 'Addons::getParameters');
    $routes->post('toggleParameterStatus/(:num)', 'Addons::toggleParameterStatus/$1');
    $routes->delete('deleteParameter/(:num)', 'Addons::deleteParameter/$1');
    $routes->post('saveParameter', 'Addons::saveParameter');
    $routes->post('saveGeniacsUrl', 'Addons::saveGeniacsUrl');

    // VPN Remote routes
    $routes->get('vpn-remote', 'Addons::vpnRemote');
    $routes->post('vpn-remote/save', 'Addons::saveVpnService');
    $routes->get('vpn-remote/detail/(:num)', 'Addons::getVpnDetail/$1');
    $routes->post('vpn-remote/update/(:num)', 'Addons::updateVpnService/$1');
    $routes->post('vpn-remote/delete/(:num)', 'Addons::deleteVpnService/$1');
    $routes->post('vpn-remote/activate/(:num)', 'Addons::activateVpnService/$1');
});

$routes->get('whatsapp', 'Settings::whatsapp');
$routes->get('whatsapp/reset', 'Whatsapp::reset');
$routes->post('whatsapp/save-device', 'Whatsapp::saveDevice');
$routes->post('whatsapp/check-status', 'Whatsapp::checkStatus');
$routes->post('whatsapp/get-qr-code', 'Whatsapp::getQrCode');
$routes->post('whatsapp/send', 'Whatsapp::send');
$routes->get('whatsapp/account', 'WhatsappMessage::accountList');
$routes->post('whatsapp/account/add', 'WhatsappMessage::addAccount');
$routes->get('whatsapp/account/qrcode/(:num)', 'WhatsappMessage::getQRCode/$1');
$routes->get('whatsapp/broadcast', 'WhatsappMessage::broadcastList');
$routes->post('whatsapp/broadcast/create', 'WhatsappMessage::createBroadcast');
$routes->post('whatsapp/broadcast/delete/(:num)', 'WhatsappMessage::deleteBroadcast/$1');
$routes->get('whatsapp/settings', 'WhatsappMessage::settings');
$routes->post('whatsapp/settings/save', 'WhatsappMessage::saveSettings');
$routes->post('whatsapp/blast/send', 'WhatsappMessage::sendBlast');
$routes->post('whatsapp/blast/count-target', 'WhatsappMessage::countTarget');
$routes->post('whatsapp/blast/preview', 'WhatsappMessage::previewTarget');
$routes->get('whatsapp/template/message', 'WhatsappMessage::templateMessage');
$routes->get('whatsapp/info', 'WhatsappMessage::systemInfo');
$routes->get('whatsapp/notification', 'WhatsappMessage::notificationView');
$routes->post('whatsapp/template/send', 'WhatsappMessage::sendTemplate');
$routes->post('whatsapp/template-message/send', 'WhatsappMessage::saveTemplates');
$routes->post('whatsapp/logout', 'Whatsapp::logout');
$routes->post('whatsapp/number/delete', 'Whatsapp::deleteNumber');
$routes->post('whatsapp', 'Whatsapp::saveNotifSettings');

// WhatsApp Billing Notification routes (API only - no menu)
$routes->get('whatsapp/billing/send-all', 'WhatsappBillingNotification::sendAllNotifications');
$routes->get('whatsapp/billing/send/(:segment)', 'WhatsappBillingNotification::sendManualNotification/$1');
$routes->get('whatsapp/billing/test', 'WhatsappBillingNotification::testNotification');
$routes->get('whatsapp/billing/send-due-date', 'WhatsappBillingNotification::sendDueDateNotifications');
$routes->get('whatsapp/billing/send-payment-confirmations', 'WhatsappBillingNotification::sendPaymentConfirmations');

// Message Log API routes
$routes->get('whatsapp/message-logs', 'WhatsappMessage::getMessageLogs');
$routes->post('whatsapp/message-logs/test', 'WhatsappMessage::addTestMessage');
$routes->post('whatsapp/message-logs/retry', 'WhatsappMessage::retryMessage');
$routes->post('whatsapp/message-logs/remove', 'WhatsappMessage::removeMessage');

// Test WhatsApp functionality
$routes->get('test-whatsapp', 'TestWhatsapp::index');

// Master Data Routes
$routes->get('master/notification-whatsapp', 'Master::notificationWhatsapp');
$routes->get('master/notification-whatsapp/detail/(:num)', 'Master::getNotificationDetail/$1');
$routes->get('master/notification-whatsapp/delete/(:num)', 'Master::deleteNotification/$1');
$routes->get('master/notification-whatsapp/retry/(:num)', 'Master::retryNotification/$1');

// Master Area Routes
$routes->get('master/area', 'Master::area');
$routes->post('master/area/data', 'Master::getAreasData');
$routes->get('master/area/detail/(:num)', 'Master::getAreaDetail/$1');
$routes->get('master/area/by-branch/(:num)', 'Master::getAreasByBranch/$1');
$routes->post('master/area/create', 'Master::createArea');
$routes->post('master/area/update/(:num)', 'Master::updateArea/$1');
$routes->delete('master/area/delete/(:num)', 'Master::deleteArea/$1');

// Master ODP Routes
$routes->get('master/odp', 'Master::odp');
$routes->post('master/odp/data', 'Master::getOdpsData');
$routes->get('master/odp/detail/(:num)', 'Master::getOdpDetail/$1');
$routes->get('master/odp/by-area/(:num)', 'Master::getOdpsByArea/$1');
$routes->get('master/odp/customers/(:num)', 'Master::getCustomersByOdp/$1');
$routes->post('master/odp/create', 'Master::createOdp');
$routes->post('master/odp/update/(:num)', 'Master::updateOdp/$1');
$routes->delete('master/odp/delete/(:num)', 'Master::deleteOdp/$1');

// Test session route
$routes->get('test-session', 'TestSession::index');

// Withdraw Routes
$routes->get('withdraw', 'Withdraw::index');
$routes->post('withdraw/data', 'Withdraw::getData', ['filter' => '']);
$routes->get('withdraw/data', 'Withdraw::getData'); // Allow GET as well
$routes->get('withdraw/available-balance', 'Withdraw::getAvailableBalance');
$routes->post('withdraw/create', 'Withdraw::create');
$routes->get('withdraw/detail/(:num)', 'Withdraw::getDetail/$1');
$routes->post('withdraw/update-status/(:num)', 'Withdraw::updateStatus/$1');
$routes->delete('withdraw/delete/(:num)', 'Withdraw::delete/$1');
// Auto disbursement routes
$routes->post('withdraw/process-disbursement/(:num)', 'Withdraw::processAutoDisbursement/$1');
$routes->get('withdraw/check-disbursement/(:num)', 'Withdraw::checkDisbursementStatus/$1');
$routes->post('withdraw/validate-bank-account', 'Withdraw::validateBankAccount');
$routes->get('withdraw/disbursement-balance', 'Withdraw::getDisbursementBalance');
// Webhook
$routes->post('webhook/disbursement/(:alpha)', 'Withdraw::webhookHandler/$1');

// Additional routes for system-info view
$routes->post('whatsapp/message/retryMessage', 'WhatsappMessage::retryMessage');
$routes->post('whatsapp/message/removeMessage', 'WhatsappMessage::removeMessage');

$routes->post('server-location/diagnose-connection', 'LokasiServer::diagnose');
$routes->post('server-location/test-alternative', 'LokasiServer::testAlternativeConnection');

// Documentation routes (accessible via profile dropdown)
$routes->get('documentation', 'Documentation::index');
$routes->get('documentation/whatsapp-billing', 'Documentation::whatsappBilling');
$routes->get('documentation/hosting-setup', 'Documentation::hostingSetup');
$routes->get('documentation/api-endpoints', 'Documentation::apiEndpoints');
$routes->get('documentation/troubleshooting', 'Documentation::troubleshooting');

// RADIUS functionality removed - Using PPP Secrets only


// Universal WhatsApp Webhook routes (Support all providers)
$routes->post('api/whatsapp/webhook', 'WhatsAppWebhookUniversal::receive'); // Universal webhook endpoint
$routes->get('api/whatsapp/webhook', 'WhatsAppWebhookUniversal::verify'); // Webhook verification (for Meta)
$routes->get('api/whatsapp/webhook/test', 'WhatsAppWebhookUniversal::test'); // Test endpoint
$routes->get('api/whatsapp/webhook/stats', 'WhatsAppWebhookUniversal::stats'); // Statistics endpoint

// Direct Customer Billing Routes (MUST BE LAST - catches any remaining segment)
// Pastikan hanya nomor layanan yang valid (minimal 8 digit angka)
$routes->get('([0-9]{8,})', 'PublicBilling::directBilling/$1', ['filter' => 'billingFilter']);

// Router Resource Management (lengkap dengan CRUD)
$routes->resource('routers', [
    'filter' => 'isLoggedIn',
    'controller' => 'Routers',
    'only' => ['show', 'new', 'create']
]);

// Router Additional Management Routes
$routes->get('routers/(:num)/details', 'Routers::details/$1', ['filter' => 'isLoggedIn']);
$routes->get('routers/(:num)/status', 'Routers::status/$1', ['filter' => 'isLoggedIn']);
$routes->post('routers/(:num)/toggle-status', 'Routers::toggleStatus/$1', ['filter' => 'isLoggedIn']);
$routes->get('routers/(:num)/export-config', 'Routers::exportConfig/$1', ['filter' => 'isLoggedIn']);
$routes->post('routers/import-config', 'Routers::importConfig', ['filter' => 'isLoggedIn']);
$routes->get('routers/bulk-actions', 'Routers::bulkActions', ['filter' => 'isLoggedIn']);
$routes->post('routers/bulk-delete', 'Routers::bulkDelete', ['filter' => 'isLoggedIn']);

// Router Monitoring & Logging
$routes->get('routers/(:num)/logs', 'Routers::logs/$1', ['filter' => 'isLoggedIn']);
$routes->get('routers/(:num)/monitoring', 'Routers::monitoring/$1', ['filter' => 'isLoggedIn']);
$routes->post('routers/(:num)/clear-logs', 'Routers::clearLogs/$1', ['filter' => 'isLoggedIn']);

// Isolir Configuration & Management Routes
$routes->get('routers/isolir-config-modal', 'Routers::isolirConfigModal', ['filter' => 'isLoggedIn']);
$routes->post('routers/setup-isolir-profile', 'Routers::setupIsolirProfile', ['filter' => 'isLoggedIn']);
$routes->post('routers/auto-isolir-customer', 'Routers::autoIsolirCustomer', ['filter' => 'isLoggedIn']);
$routes->post('routers/restore-customer/(:num)', 'Routers::restoreCustomer/$1', ['filter' => 'isLoggedIn']);
$routes->get('routers/isolir-status/(:num)', 'Routers::isolirStatus/$1', ['filter' => 'isLoggedIn']);

// Laporan routes
$routes->get('laporan', 'Laporan::index', ['filter' => 'isLoggedIn']);

// Internet Packages submenus
$routes->group('internet-packages', ['filter' => 'isLoggedIn'], function ($routes) {
    // Bandwidth management
    $routes->get('bandwidth', 'InternetPackages::bandwidth');
    $routes->get('bandwidth/(:num)', 'InternetPackages::getBandwidth/$1');
    $routes->post('bandwidth/create', 'InternetPackages::createBandwidth');
    $routes->put('bandwidth/(:num)', 'InternetPackages::updateBandwidth/$1');
    $routes->post('bandwidth/update/(:num)', 'InternetPackages::updateBandwidth/$1');
    $routes->delete('bandwidth/(:num)', 'InternetPackages::deleteBandwidth/$1');
    $routes->get('bandwidth/data', 'InternetPackages::getBandwidthData');
    $routes->get('bandwidth/list-all', 'InternetPackages::listBandwidthProfiles');

    // Group Profile management
    $routes->get('group-profile', 'InternetPackages::groupProfile');
    $routes->get('group-profile/(:num)', 'InternetPackages::getGroupProfile/$1');
    $routes->post('group-profile/create', 'InternetPackages::createGroupProfile');
    $routes->put('group-profile/(:num)', 'InternetPackages::updateGroupProfile/$1');
    $routes->post('group-profile/update/(:num)', 'InternetPackages::updateGroupProfile/$1');
    $routes->delete('group-profile/(:num)', 'InternetPackages::deleteGroupProfile/$1');
    $routes->get('group-profile/data', 'InternetPackages::getGroupProfileData');

    // MikroTik sync routes
    $routes->post('group-profile/sync-to-router', 'InternetPackages::syncGroupProfileToSpecificRouter');
    $routes->post('group-profile/verify-sync', 'InternetPackages::verifyGroupProfileOnRouter');
    $routes->post('group-profile/sync-all', 'InternetPackages::syncAllGroupProfilesToMikroTik');
    $routes->get('mikrotik-profiles/(:num)', 'InternetPackages::getMikroTikProfilesFromRouter/$1');
    $routes->post('mikrotik-profiles/cleanup/(:num)', 'InternetPackages::cleanupMikroTikProfiles/$1');
    $routes->post('mikrotik-profiles/sync-all/(:num)', 'InternetPackages::syncAllGroupProfilesToSpecificRouter/$1');
    $routes->get('mikrotik-profiles/load', 'InternetPackages::loadMikroTikProfiles');
    $routes->post('group-profile/sync/(:num)', 'InternetPackages::syncGroupProfileToMikroTik/$1');
    $routes->get('routers/data', 'InternetPackages::getRouterData');

    // Package Profile management
    $routes->get('package-profile', 'InternetPackages::packageProfile');
    $routes->get('package-profile/data', 'InternetPackages::getPackageProfileData');
    $routes->get('package-profile/(:num)', 'InternetPackages::getPackageProfile/$1');
    $routes->post('package-profile/create', 'InternetPackages::createPackageProfile');
    $routes->put('package-profile/(:num)', 'InternetPackages::updatePackageProfile/$1');
    $routes->delete('package-profile/(:num)', 'InternetPackages::deletePackageProfile/$1');

    // MikroTik Profile Sync for Package Profile
    $routes->get('package-profile/mikrotik-profiles', 'InternetPackages::getMikroTikProfiles');
    $routes->post('package-profile/sync-mikrotik', 'InternetPackages::syncMikroTikProfiles');
});

// Test routes
$routes->get('test-midrotik', 'TestMikroTik::syncTest');
$routes->get('customers/test-update/(:num)', 'Customer::testUpdate/$1', ['filter' => 'isLoggedIn']);
$routes->post('customers/test-update/(:num)', 'Customer::testUpdate/$1', ['filter' => 'isLoggedIn']);
$routes->get('customers/direct-test/(:num)', 'Customer::directUpdateTest/$1', ['filter' => 'isLoggedIn']);

// Payment callback testing routes
$routes->get('test-payment-callback', 'TestPaymentCallback::index');
$routes->post('test-payment-callback/create-test-invoice', 'TestPaymentCallback::createTestInvoice');
$routes->post('test-payment-callback/simulate-callback', 'TestPaymentCallback::simulateCallback');
$routes->get('test-payment-callback/check-invoice-status', 'TestPaymentCallback::checkInvoiceStatus');

// Debug routes
$routes->get('customer/debugPackageData', 'Customer::debugPackageData');

// Kategori Kas routes
$routes->group('kategori', ['filter' => 'isLoggedIn'], function ($routes) {
    $routes->get('list', 'ArusKas::listKategori');
    $routes->post('store', 'ArusKas::storeKategori');
    $routes->get('edit/(:num)', 'ArusKas::editKategori/$1');
    $routes->delete('delete/(:num)', 'ArusKas::deleteKategori/$1');
});


// Clustering routes
$routes->group('clustering', ['filter' => 'isLoggedIn'], function ($routes) {
    $routes->get('/', 'Cluster::index');
    $routes->get('create', 'Cluster::create');
    $routes->get('all', 'Cluster::all');
    $routes->post('store', 'Cluster::store');
    $routes->get('(:num)', 'Cluster::show/$1');
    $routes->get('(:num)/edit', 'Cluster::edit/$1');
    $routes->put('(:num)', 'Cluster::update/$1');
    $routes->post('(:num)', 'Cluster::update/$1'); // For method spoofing
    $routes->delete('(:num)', 'Cluster::delete/$1');
});

// Dashboard API Routes
$routes->group('api/dashboard', function ($routes) {
    $routes->get('statistics', 'Api\Dashboard::statistics');
    $routes->get('customer-stats', 'Api\Dashboard::customerStats');
    $routes->get('payment-stats', 'Api\Dashboard::paymentStats');
    $routes->get('system-info', 'Api\Dashboard::systemInfo');
    $routes->get('financial-chart', 'Api\Dashboard::financialChart');

    // Add the kebab-case version of realSystemInfo
    $routes->get('real-system-info', 'Api\Dashboard::realSystemInfo');
});
$routes->post('api/dashboard/financial-data', 'Api\Dashboard::getFinancialData');

// MikroTik PPPoE Profiles API
$routes->get('api/mikrotik/pppoe-profiles', 'MikroTikProfile::load');
// Routing untuk customer portal via subdomain
$routes->add('/', function () {
    $host = $_SERVER['HTTP_HOST'] ?? '';
    if (preg_match('/^([a-zA-Z0-9_-]+)\\.difihome\\.my\\.id$/', $host, $matches)) {
        // Jika subdomain, arahkan ke CustomerDashboard::subdomainIndex
        return (new \App\Controllers\CustomerDashboard())->subdomainIndex();
    }
    // Fallback ke Home::index
    return (new \App\Controllers\Home())->index();
});
