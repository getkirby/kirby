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
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier GmbH
 * @license   https://getkirby.com/license
 */
class Users extends Collection
{
    /**
     * All registered users methods
     *
     * @var array
     */
    public static $methods = [];

    public function create(array $data)
    {
        return User::create($data);
    }

    /**
     * Adds a single user or
     * an entire second collection to the
     * current collection
     *
     * @param mixed $object
     * @return self
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
        } elseif (is_a($object, 'Kirby\Cms\User') === true) {
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
    public static function factory(array $users, array $inject = [])
    {
        $collection = new static();

        // read all user blueprints
        foreach ($users as $props) {
            $user = User::factory($props + $inject);
            $collection->set($user->id(), $user);
        }

        return $collection;
    }

    /**
     * Finds a user in the collection by id or email address
     *
     * @param string $key
     * @return \Kirby\Cms\User|null
     */
    public function findByKey(string $key)
    {
        if (Str::contains($key, '@') === true) {
            return parent::findBy('email', strtolower($key));
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
    public static function load(string $root, array $inject = [])
    {
        $users = new static();

        foreach (Dir::read($root) as $userDirectory) {
            if (is_dir($root . '/' . $userDirectory) === false) {
                continue;
            }

            // get role information
            if (file_exists($root . '/' . $userDirectory . '/index.php') === true) {
                $credentials = require $root . '/' . $userDirectory . '/index.php';
            }

            // create user model based on role
            $user = User::factory([
                'id'    => $userDirectory,
                'model' => $credentials['role'] ?? null
            ] + $inject);

            $users->set($user->id(), $user);
        }

        return $users;
    }

    /**
     * Shortcut for `$users->filterBy('role', 'admin')`
     *
     * @param string $role
     * @return self
     */
    public function role(string $role)
    {
        return $this->filterBy('role', $role);
    }
}
