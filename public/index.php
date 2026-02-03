<?php

// Autoload Core Classes
spl_autoload_register(function ($class) {
    $paths = [
        __DIR__ . '/../app/Core/' . $class . '.php',
        __DIR__ . '/../app/Controllers/' . $class . '.php',
        __DIR__ . '/../app/Models/' . $class . '.php',
    ];

    foreach ($paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            return;
        }
    }
});

// Set timezone
date_default_timezone_set('Asia/Jakarta');

// Start session
session_start();

// Initialize Router
$router = new Router();

// ============= PUBLIC ROUTES =============
$router->get('/', 'HomeController@index');
$router->get('/room/:id', 'RoomController@detail');
$router->get('/booking/form/:roomId', 'BookingController@form');
$router->post('/booking/submit', 'BookingController@submit');
$router->get('/booking/status', 'BookingController@statusPage');
$router->post('/booking/check', 'BookingController@checkStatus');
$router->post('/booking/cancel', 'BookingController@cancel');

// Search & Filter
$router->get('/search', 'HomeController@search');

// Lobby Display
$router->get('/lobby', 'LobbyController@display');

// ============= ADMIN ROUTES =============
$router->get('/admin', 'Admin/AdminController@dashboard');
$router->get('/admin/login', 'Admin/AdminController@loginPage');
$router->post('/admin/login', 'Admin/AdminController@login');
$router->get('/admin/logout', 'Admin/AdminController@logout');

// Admin Bookings
$router->get('/admin/bookings', 'Admin/BookingController@index');
$router->post('/admin/bookings/approve/:id', 'Admin/BookingController@approve');
$router->post('/admin/bookings/reject/:id', 'Admin/BookingController@reject');

// Admin Rooms
$router->get('/admin/rooms', 'Admin/RoomController@index');
$router->get('/admin/rooms/create', 'Admin/RoomController@create');
$router->post('/admin/rooms/store', 'Admin/RoomController@store');
$router->get('/admin/rooms/edit/:id', 'Admin/RoomController@edit');
$router->post('/admin/rooms/update/:id', 'Admin/RoomController@update');
$router->post('/admin/rooms/delete/:id', 'Admin/RoomController@delete');

// Resolve Route
$router->resolve();
