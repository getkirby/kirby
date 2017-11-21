<?php

require '../../vendor/autoload.php';

use Kirby\Http\Router;
use Kirby\Http\Router\Route;

$router = new Router([
    new Route('/', 'GET', function () {
        return 'index';
    }),
    new Route('login', 'GET', function () {
        return 'login';
    }),
    new Route('logout', 'GET', function () {
        return 'logout';
    }),
    new Route('users/(:any)', 'GET', function ($username) {
        return $username;
    }),
]);

// var_dump($router->call('login', 'GET'));
// var_dump($router->call('logout', 'GET'));
var_dump($router->call('users/homer-simpson', 'GET'));
