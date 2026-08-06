<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'Home::index');

$routes->get('authorize', 'Auth::authorize');
$routes->post('login', 'Auth::attemptLogin');