<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'Home::index');

$routes->get('authorize', 'Auth::authorize');
$routes->post('login', 'Auth::attemptLogin');

// Register
$routes->get('register', 'Register::index');
$routes->post('register/process', 'Register::process');

// Forgot & Reset Password
$routes->get('forgot-password', 'ForgotPassword::index');
$routes->post('forgot-password/process', 'ForgotPassword::processEmail');
$routes->get('reset-password', 'ForgotPassword::reset');
$routes->post('reset-password/process', 'ForgotPassword::processReset');

// SSO Protocol Endpoints
$routes->get('public-key', 'Sso::publicKey');
$routes->post('refresh-token', 'Sso::refreshToken');
$routes->post('logout', 'Sso::logout');

// Development-only: Testing endpoint (guard di controller)
$routes->post('api/test-login', 'Auth::testLogin');