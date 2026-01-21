<?php

namespace Config;

if (!isset($routes)) {
    $routes = \Config\Services::routes();
}

$routes->group('arus_kas', ['namespace' => 'App\Controllers'], static function ($routes) {
    $routes->get('/', 'ArusKasController::index');
    $routes->get('data', 'ArusKasController::getData');
    $routes->post('flowSave', 'ArusKasController::save');
    $routes->match(['post', 'delete'], 'delete/(:num)', 'ArusKasController::delete/$1'); // Allow both POST and DELETE
    $routes->delete('deleteAll', 'ArusKasController::deleteAll');
    $routes->get('getFlow/(:num)', 'ArusKasController::getFlow/$1');
});
