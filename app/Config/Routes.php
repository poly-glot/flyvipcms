<?php

declare(strict_types=1);

use CodeIgniter\Router\RouteCollection;

/* @var RouteCollection $routes */
$routes->get('/', 'Home::index');

service('auth')->routes($routes, ['except' => ['register', 'magic-link', 'logout']]);
$routes->post('logout', '\CodeIgniter\Shield\Controllers\LoginController::logoutAction');

$routes->get('account/password', 'Account::password', ['filter' => 'session']);
$routes->post('account/password', 'Account::updatePassword', ['filter' => 'session']);

$routes->get('flights/taken-seats', 'FlightSeats::taken', ['filter' => 'session']);

$crudResources = [
    'aircraft-types' => 'AircraftTypes',
    'aircrafts' => 'Aircrafts',
    'maintenance' => 'AircraftRepairs',
    'airports' => 'Airports',
    'routes' => 'AirRoutes',
    'pilots' => 'Pilots',
    'pilot-documents' => 'PilotDocuments',
    'pilot-certifications' => 'PilotCertifications',
    'flight-schedule' => 'PilotFlights',
];

$routes->group('admin', ['filter' => 'group:admin', 'namespace' => 'App\Controllers\Admin'], static function (RouteCollection $routes) use ($crudResources): void {
    $routes->get('/', 'Dashboard::index');

    foreach ($crudResources as $slug => $controller) {
        $routes->get($slug, "{$controller}::index");
        $routes->get("{$slug}/new", "{$controller}::new");
        $routes->post($slug, "{$controller}::create");
        $routes->get("{$slug}/(:num)/edit", "{$controller}::edit/$1");
        $routes->post("{$slug}/(:num)", "{$controller}::update/$1");
        $routes->post("{$slug}/(:num)/delete", "{$controller}::delete/$1");
    }

    $routes->get('members', 'Members::index');
    $routes->get('members/new', 'Members::new');
    $routes->post('members', 'Members::create');
    $routes->get('members/(:num)', 'Members::show/$1');
    $routes->get('members/(:num)/edit', 'Members::edit/$1');
    $routes->post('members/(:num)', 'Members::update/$1');
    $routes->post('members/(:num)/status', 'Members::status/$1');
    $routes->post('members/(:num)/sub-members', 'Members::addSubMember/$1');
    $routes->post('members/(:num)/delete', 'Members::delete/$1');

    $routes->get('payments', 'Payments::index');
    $routes->get('payments/new', 'Payments::new');
    $routes->post('payments', 'Payments::create');
    $routes->get('payments/(:num)', 'Payments::show/$1');

    $routes->get('points', 'Points::index');
    $routes->get('points/adjust', 'Points::adjust');
    $routes->post('points/adjust', 'Points::storeAdjustment');
    $routes->get('points/transfer', 'Points::transfer');
    $routes->post('points/transfer', 'Points::storeTransfer');

    $routes->get('reservations', 'Reservations::index');
    $routes->get('reservations/new', 'Reservations::new');
    $routes->post('reservations', 'Reservations::create');
    $routes->get('reservations/(:num)', 'Reservations::show/$1');
    $routes->post('reservations/(:num)/confirm', 'Reservations::confirm/$1');
    $routes->post('reservations/(:num)/cancel', 'Reservations::cancel/$1');
});

$routes->group('portal', ['filter' => 'group:member,submember', 'namespace' => 'App\Controllers\Portal'], static function (RouteCollection $routes): void {
    $routes->get('/', 'Dashboard::index');
    $routes->get('points', 'Points::index');
    $routes->get('profile', 'Profile::edit');
    $routes->post('profile', 'Profile::update');
    $routes->get('reservations', 'Reservations::index');
    $routes->get('reservations/new', 'Reservations::new');
    $routes->post('reservations', 'Reservations::create');
    $routes->post('reservations/(:num)/confirm', 'Reservations::confirm/$1');
    $routes->post('reservations/(:num)/cancel', 'Reservations::cancel/$1');
});
