<?php

namespace Kirby\Cms;

class Users extends Collection
{
    protected static $accept = User::class;

    public function create(array $data)
    {
        // move this into a UsersStore class
        $data['store'] = UserStore::class;

        return (new User($data))->create();
    }

}
