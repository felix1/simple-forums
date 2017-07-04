<?php

/**
 * Default Auth-specific routes.
 */
$routes->group('', ['namespace' => 'Myth\Auth\Controllers'], function($routes)
{
	$routes->get('login', 'Auth::login');
	$routes->post('login', 'Auth::attemptLogin');
	$routes->get('register', 'Auth::register');
	$routes->post('register', 'Auth::attemptRegister');
});

