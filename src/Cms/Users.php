<?php

namespace Kirby\Cms;

use Kirby\Toolkit\Dir;
use Kirby\Toolkit\Str;

/**
 * Extension of the Collection class that
 * provides a Users::factory method to convert
 * an array into a Users collection with User
 * objects and a Users::load method to load
 * user accounts from disk.
 */
class Users extends Collection
{
    protected static $accept = User::class;

    public function create(array $data)
    {
        // move this into a UsersStore class
        $data['store'] = UserStore::class;

        return User::create($data);
    }

    public static function factory(array $users, array $inject = []): self
    {
        $collection = new static;

        // read all user blueprints
        foreach ($users as $props) {
            $user = new User($props + $inject);
            $collection->set($user->id(), $user);
        }

        return $collection;
    }

    public function findByKey($key)
    {
        if (Str::contains($key, '@') === true) {
            $key = sha1($key);
        }

        return parent::findByKey($key);
    }

    public static function load(string $root, array $inject = []): self
    {
        $users = new static;

        foreach (Dir::read($root) as $userDirectory) {
            if (is_dir($root . '/' . $userDirectory) === false) {
                continue;
            }

            $user = new User([
                'email' => $userDirectory,
                'store' => UserStore::class
            ] + $inject);

            $users->set($user->id(), $user);
        }

        return $users;
    }
}
