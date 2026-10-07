<?php

namespace Config;

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'Home::index');
$routes->get('account', 'Account::index');
$routes->post('register', 'Account::register');
$routes->post('login', 'Account::login');
$routes->post('logout', 'Account::logout');
