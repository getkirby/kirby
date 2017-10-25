<?php

use Kirby\Cms\Avatar;
use Kirby\Cms\User;
use Kirby\Cms\Users;
use Kirby\FileSystem\Folder;

return [
    'users' => function (): Users {

        $folder = new Folder($this->root('accounts'));
        $users  = [];

        foreach ($folder->folders() as $root) {
            $users[] = new User([
                'id'   => basename($root),
                'root' => $root,
            ]);
        }

        return new Users($users);

    }
];
