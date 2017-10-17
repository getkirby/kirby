<?php

require '../../vendor/autoload.php';

use Kirby\FileSystem\Folder;
use Kirby\Users\Users;
use Kirby\Users\User;
use Kirby\Users\User\Auth\Password as Auth;
use Kirby\Users\User\Avatar;
use Kirby\Users\User\Store;

$folder = new Folder(__DIR__ . '/accounts');
$users  = [];

foreach ($folder->folders() as $root) {
    $users[] = [
        'id'     => $id = basename($root),
        'auth'   => new Auth,
        'store'  => new Store(['root' => $root]),
        'avatar' => new Avatar([
            'url'  => '/files/users/' . $id . '/' . $id . '.jpg',
            'root' => $root . '/' . $id . '.jpg'
        ])
    ];
}

$users = new Users($users);


var_dump($users->last()->avatar()->url());
