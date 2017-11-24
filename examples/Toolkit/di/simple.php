<?php

require '../vendor/autoload.php';

use Kirby\Toolkit\DI\Dependencies;

class App extends Dependencies {}

$app = new App;

$app->set('db', function () {
    return new PDO('mysql:host=127.0.0.1;dbname=example;charset=utf8', 'root', 'root');
});

$app->set('url', 'https://yourapp.com');
$app->set('root', __DIR__);

var_dump($app->get('db')->query('Select * from `users`'));
var_dump($app->get('url'));
var_dump($app->get('root'));
