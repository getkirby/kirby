<?php

namespace Kirby\Cms;

use Kirby\Toolkit\Dir;
use Kirby\Toolkit\Str;

/**
 * The `$users` object refers to a collection
 * of users with or without Panel access. Like
 * all collections, you can filter, modify,
 * convert or check the users collection.
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 */
class Users extends Collection
{
    public function create(array $data)
    {
        return User::create($data);
    }

    /**
     * Adds a single user or
     * an entire second collection to the
     * current collection
     *
     * @param mixed $item
     * @return Users
     */
    public function add($object)
    {
        // add a page collection
        if (is_a($object, static::class) === true) {
            $this->data = array_merge($this->data, $object->data);

        // add a user by id
        } elseif (is_string($object) === true && $user = App::instance()->user($object)) {
            $this->__set($user->id(), $user);

        // add a user object
        } elseif (is_a($object, User::class) === true) {
            $this->__set($object->id(), $object);
        }

        return $this;
    }

    /**
     * Takes an array of user props and creates a nice and clean user collection from it
     *
     * @param array $users
     * @param array $inject
     * @return self
     */
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

    /**
     * Finds a user in the collection by id or email address
     *
     * @param string $key
     * @return User|null
     */
    public function findByKey($key)
    {
        if (Str::contains($key, '@') === true) {
            return parent::findBy('email', $key);
        }

        return parent::findByKey($key);
    }

    /**
     * Loads a user from disk by passing the absolute path (root)
     *
     * @param string $root
     * @param array $inject
     * @return self
     */
    public static function load(string $root, array $inject = []): self
    {
        $users = new static;

        foreach (Dir::read($root) as $userDirectory) {
            if (is_dir($root . '/' . $userDirectory) === false) {
                continue;
            }

            $user = new User([
                'id' => $userDirectory,
            ] + $inject);

            $users->set($user->id(), $user);
        }

        return $users;
    }
}
