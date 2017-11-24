<?php

require '../../vendor/autoload.php';

use Kirby\Http\Router;
use Kirby\Http\Router\Route;

class App
{
    public function title()
    {
        return 'The Simpsons';
    }
}

$router = new Router([
    new Route('users/(:any)', 'GET', function ($username, App $app) {
        return $app->title() . ': ' . $username;
    })
]);

$router->dependency('App', 'App');

var_dump($router->call('users/homer-simpson', 'GET'));
