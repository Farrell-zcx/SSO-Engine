<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'Home::index');

$routes->get('authorize', 'Auth::authorize');
$routes->post('authorize/continue', 'Auth::continueAsUser');
$routes->get('authorize/switch', 'Auth::switchAccount');
$routes->post('login', 'Auth::attemptLogin');



// Forgot & Reset Password
$routes->get('forgot-password', 'ForgotPassword::index');
$routes->post('forgot-password/process', 'ForgotPassword::processEmail');
$routes->get('reset-password', 'ForgotPassword::reset');
$routes->post('reset-password/process', 'ForgotPassword::processReset');

// SSO Protocol Endpoints
$routes->get('public-key', 'Sso::publicKey');
$routes->post('refresh-token', 'Sso::refreshToken');
$routes->post('logout', 'Sso::logout');
$routes->get('logout-web', 'Auth::logoutWeb');

// Development-only: Testing endpoint (guard di controller)
$routes->post('api/test-login', 'Auth::testLogin');

// Admin Dashboard (Dilindungi middleware sso_admin)
$routes->get('authorize-admin', 'AuthAdminController::index');
$routes->post('authorize-admin/login', 'AuthAdminController::login');
$routes->get('authorize-admin/logout', 'AuthAdminController::logout');

$routes->group('admin', ['filter' => 'sso_admin'], static function ($routes) {
    $routes->get('users', 'AdminUser::index');
    $routes->post('users', 'AdminUser::store');
    $routes->post('users/(:segment)/delete', 'AdminUser::delete/$1');
    $routes->get('users/(:segment)/access', 'AdminUser::access/$1');
    $routes->post('users/(:segment)/access', 'AdminUser::grantAccess/$1');
    $routes->post('users/(:segment)/access/(:segment)/revoke', 'AdminUser::revokeAccess/$1/$2'); // Menggunakan POST untuk kemudahan form HTML
});