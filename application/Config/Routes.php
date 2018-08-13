<?php namespace Config;

/**
 * --------------------------------------------------------------------
 * URI Routing
 * --------------------------------------------------------------------
 * This file lets you re-map URI requests to specific controller functions.
 *
 * Typically there is a one-to-one relationship between a URL string
 * and its corresponding controller class/method. The segments in a
 * URL normally follow this pattern:
 *
 *    example.com/class/method/id
 *
 * In some instances, however, you may want to remap this relationship
 * so that a different class/function is called than the one
 * corresponding to the URL.
 *
 */

// Create a new instance of our RouteCollection class.
$routes = Services::routes(true);

// Load the system's routing file first, so that the app and ENVIRONMENT
// can override as needed.
if (file_exists(BASEPATH.'Config/Routes.php'))
{
	require BASEPATH.'Config/Routes.php';
}

/**
 * --------------------------------------------------------------------
 * Router Setup
 * --------------------------------------------------------------------
 * The RouteCollection object allows you to modify the way that the
 * Router works, by acting as a holder for it's configuration settings.
 * The following methods can be called on the object to modify
 * the default operations.
 *
 *    $routes->defaultNamespace()
 *
 * Modifies the namespace that is added to a controller if it doesn't
 * already have one. By default this is the global namespace (\).
 *
 *    $routes->defaultController()
 *
 * Changes the name of the class used as a controller when the route
 * points to a folder instead of a class.
 *
 *    $routes->defaultMethod()
 *
 * Assigns the method inside the controller that is ran when the
 * Router is unable to determine the appropriate method to run.
 *
 *    $routes->setAutoRoute()
 *
 * Determines whether the Router will attempt to match URIs to
 * Controllers when no specific route has been defined. If false,
 * only routes that have been defined here will be available.
 */
$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Home');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override();
$routes->setAutoRoute(false);
$routes->discoverLocal(false);

/**
 * --------------------------------------------------------------------
 * Route Definitions
 * --------------------------------------------------------------------
 */

// We get a performance increase by specifying the default
// route since we don't have to scan directories.
$routes->add('/', 'Home::index');

// Auth
$routes->group('', ['namespace' => 'App\Controllers'], function($routes) {
	// Login/out
	$routes->get('login', 'AuthController::login', ['as' => 'login']);
	$routes->post('login', 'AuthController::attemptLogin');
	$routes->get('logout', 'AuthController::logout');

	// Registration
	$routes->get('register', 'AuthController::register', ['as' => 'register']);
	$routes->post('register', 'AuthController::attemptRegister');

	// Forgot/Resets
	$routes->get('forgot', 'AuthController::forgotPassword', ['as' => 'forgot']);
	$routes->post('forgot', 'AuthController::attemptForgot');
	$routes->get('reset-password', 'AuthController::resetPassword', ['as' => 'reset-password']);
	$routes->post('reset-password', 'AuthController::attemptReset');
});

// Users
$routes->get('people/(:segment)', 'UserController::show/$1', ['as' => 'userProfile']);

// Forums
$routes->get('categories', 'ForumController::showCategories');
$routes->get('recent', 'ForumController::showRecent');
$routes->get('forums/(:segment)', 'ForumController::showForum/$1', ['as' => 'forumLink']);
$routes->get('topic/(:segment)', 'ThreadController::show/$1', ['as' => 'threadLink']);
$routes->get('forums/(:num)/new_post', 'PostController::newPost/$1', ['as' => 'newPost']);

/**
 * --------------------------------------------------------------------
 * Additional Routing
 * --------------------------------------------------------------------
 *
 * There will often be times that you need additional routing and you
 * need to it be able to override any defaults in this file. Environment
 * based routes is one such time. require() additional route files here
 * to make that happen.
 *
 * You will have access to the $routes object within that file without
 * needing to reload it.
 */
if (file_exists(APPPATH.'Config/'.ENVIRONMENT.'/Routes.php'))
{
	require APPPATH.'Config/'.ENVIRONMENT.'/Routes.php';
}
