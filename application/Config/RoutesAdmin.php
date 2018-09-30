<?php

/*
 * Admin area
 */
$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], function($routes)
{
	$routes->get('home', 'DashboardController::index');
});
