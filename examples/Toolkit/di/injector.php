<?php

require '../vendor/autoload.php';

use Kirby\Toolkit\DI\Dependencies;

class User {};
class Db   {};
class App  {};

$app = new App;

$dependencies = new Dependencies;
$dependencies->set('User', 'User');
$dependencies->set('Db', 'Db');
$dependencies->set('C', 'some string');

// closure
$func = function ($a, DB $db, User $user, $b, $c) {
    var_dump($a, $db, $user, $b, $c);
};

$result = $dependencies->call($func, [
    'a' => 'a',
    'b' => 'b'
], $app);


// class method
class Router
{
    public function run($a, DB $db, User $user, $b, $c)
    {
        var_dump($a, $db, $user, $b, $c, $this);
    }
}

$router = new Router;

$result = $dependencies->call([$router, 'run'], [
    'a' => 'a',
    'b' => 'b'
]);
