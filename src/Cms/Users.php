<?php

namespace Kirby\Cms;

use Kirby\Util\Dir;

class Users extends Collection
{
    protected static $accept = User::class;

    public function create(array $data)
    {
        // move this into a UsersStore class
        $data['store'] = UserStore::class;

        return User::create($data);
    }

    public static function factory(App $app = null): self
    {

        $app   = $app ?? App::instance();
        $root  = $app->root('accounts');
        $users = new static;

        foreach (Dir::read($root) as $userDirectory) {
            if (is_dir($root . '/' . $userDirectory) === false) {
                continue;
            }

            $user = new User([
                'email' => $userDirectory,
                'kirby' => $app,
                'store' => UserStore::class
            ]);

            $users->set($user->id(), $user);
        }

        return $users;

    }

}
