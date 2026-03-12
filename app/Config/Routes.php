<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

// Admin: login (no auth required)
$routes->get('admin/login', 'LoginController::index');
$routes->post('admin/login/process', 'LoginController::processLogin');
$routes->get('admin/login/logout', 'LoginController::logout');

// Admin: dashboard and menu (auth required)
$routes->get('admin/dashboard', 'Admin\DashboardController::index', ['filter' => 'auth']);
$routes->get('admin/menu', 'Admin\MenuController::index', ['filter' => 'auth']);
$routes->post('admin/menu/add', 'Admin\MenuController::add', ['filter' => 'auth']);
