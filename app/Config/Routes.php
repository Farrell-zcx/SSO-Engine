<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'Home::index');

$routes->get('authorize', 'Auth::authorize');
$routes->post('login', 'Auth::attemptLogin');

// SSO Protocol Endpoints
$routes->get('public-key', 'Sso::publicKey');
$routes->post('refresh-token', 'Sso::refreshToken');
$routes->post('logout', 'Sso::logout');

// Development-only: Testing endpoint (guard di controller)
$routes->post('api/test-login', 'Auth::testLogin');