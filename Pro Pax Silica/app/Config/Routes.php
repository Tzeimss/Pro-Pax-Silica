<?php

namespace Config;

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'Home::index');
$routes->get('account', 'Account::index');
$routes->post('register', 'Account::register');
$routes->post('login', 'Account::login');
$routes->post('logout', 'Account::logout');
$routes->get('portal', 'Portal::index');
$routes->post('portal/book', 'Portal::book');
$routes->post('portal/bookings/(:num)/cancel', 'Portal::cancelBooking/$1');
$routes->post('portal/profile', 'Portal::updateProfile');
$routes->post('portal/password', 'Portal::changePassword');
$routes->post('portal/employees', 'Portal::createEmployee');
$routes->post('portal/users/(:num)/toggle', 'Portal::toggleUser/$1');
$routes->post('portal/users/(:num)', 'Portal::updateUser/$1');
$routes->post('portal/schedules/(:num)', 'Portal::updateSchedule/$1');
$routes->post('portal/announcements', 'Portal::createAnnouncement');
$routes->post('portal/tickets', 'Portal::createTicket');
$routes->post('portal/tickets/(:num)', 'Portal::updateTicket/$1');
$routes->post('portal/catalog/(:num)', 'Portal::updateCatalog/$1');
$routes->post('portal/catalog', 'Portal::createCatalogItem');
$routes->post('portal/payments', 'Portal::recordPayment');
$routes->post('portal/check-in', 'Portal::checkIn');
$routes->post('portal/incidents', 'Portal::reportIncident');
$routes->post('portal/notifications/read', 'Portal::markNotificationsRead');
