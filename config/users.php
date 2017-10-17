<?php

use Kirby\FileSystem\Folder;
use Kirby\Users\User;
use Kirby\Users\User\Auth\Password as Auth;
use Kirby\Users\User\Avatar;
use Kirby\Users\User\Store;
use Kirby\Users\Users;

return function () {

    $folder = new Folder($this->root('accounts'));
    $users  = [];

    foreach ($folder->folders() as $root) {
        $users[] = [
            'id'     => $id = basename($root),
            'auth'   => new Auth,
            'store'  => new Store(['root' => $root]),
            'avatar' => new Avatar([
                'url'  => $this->url() . '/site/accounts/' . $id . '/profile.jpg',
                'root' => $root . '/profile.jpg'
            ])
        ];
    }

    return new Users($users);

};
