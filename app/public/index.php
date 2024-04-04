<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Access-Control-Allow-Methods: *");

error_reporting(E_ALL);
ini_set("display_errors", 1);

require __DIR__ . '/../vendor/autoload.php';

// Create Router instance
$router = new \Bramus\Router\Router();

$router->setNamespace('Controllers');

// routes for the appointment endpoint
$router->get('/appointments', 'AppointmentController@getAll');
$router->get('/appointments/(\d+)', 'AppointmentController@getOne');
$router->post('/appointments', 'AppointmentController@create');
$router->put('/appointments/(\d+)', 'AppointmentController@update');
$router->delete('/appointments/(\d+)', 'AppointmentController@delete');

// routes for the tutor endpoint
$router->get('/tutors', 'TutorController@getAll');
$router->get('/tutors/(\d+)', 'TutorController@getOne');
$router->post('/tutors', 'TutorController@create');
$router->put('/tutors/(\d+)', 'TutorController@update');
$router->delete('/tutors/(\d+)', 'TutorController@delete');

//route for appointment slots endpoint
$router->get('tutors/(\d+)/availability', 'TutorController@getAvailableSlotsForTutor');

//route for user endpoint
$router->post('/login', 'UserController@login');
$router->post('/register', 'UserController@register');
$router->get('/users', 'UserController@getAll');
$router->get('/users/(\d+)', 'UserController@getOne');
$router->put('/users/(\d+)', 'UserController@update');
$router->delete('/users/(\d+)', 'UserController@delete');
// Run it!
$router->run();