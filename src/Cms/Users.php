<?php

namespace Kirby\Cms;

use Kirby\Exception\InvalidArgumentException;
use Kirby\Filesystem\Dir;
use Kirby\Filesystem\F;
use Kirby\Toolkit\Str;
use Kirby\Uuid\HasUuids;

/**
 * The `$users` object refers to a collection
 * of users with or without Panel access. Like
 * all collections, you can filter, modify,
 * convert or check the users collection.
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 *
 * @template TUser of \Kirby\Cms\User
 * @extends \Kirby\Cms\Collection<TUser>
 */
class Users extends Collection
{
	use HasUuids;

	/**
	 * All registered users methods
	 */
	public static array $methods = [];

	public function create(array $data): User
	{
		return User::create($data);
	}

	/**
	 * Adds a single user or
	 * an entire second collection to the
	 * current collection
	 *
	 * @param \Kirby\Cms\Users<TUser>|TUser|string $object
	 * @return $this
	 * @throws \Kirby\Exception\InvalidArgumentException When no `User` or `Users` object or an ID of an existing user is passed
	 */
	public function add($object): static
	{
		// add a users collection
		if ($object instanceof self) {
			$this->data = [...$this->data, ...$object->data];

		// add a user by id
		} elseif (
			is_string($object) === true &&
			$user = App::instance()->user($object)
		) {
			$this->__set($user->id(), $user);

		// add a user object
		} elseif ($object instanceof User) {
			$this->__set($object->id(), $object);

		// give a useful error message on invalid input;
		// silently ignore "empty" values for compatibility with existing setups
		} elseif (in_array($object, [null, false, true], true) !== true) {
			throw new InvalidArgumentException(
				message: 'You must pass a Users or User object or an ID of an existing user to the Users collection'
			);
		}

		return $this;
	}

	/**
	 * Takes an array of user props and creates a nice
	 * and clean user collection from it
	 */
	public static function factory(array $users, array $inject = []): static
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
	 * Returns all files of all users
	 */
	public function files(): Files
	{
		$files = new Files([], $this->parent);

		foreach ($this->data as $user) {
			foreach ($user->files() as $fileKey => $file) {
				$files->data[$fileKey] = $file;
			}
		}

		return $files;
	}

	/**
	 * Finds a user in the collection by ID or email address
	 * @internal Use `$users->find()` instead
	 * @return TUser|null
	 */
	public function findByKey(string $key): User|null
	{
		if ($user = $this->findByUuid($key, 'user')) {
			return $user;
		}

		if (Str::contains($key, '@') === true) {
			return parent::findBy('email', Str::lower($key));
		}

		return parent::findByKey($key);
	}

	/**
	 * Loads a user from disk by passing the absolute path (root)
	 */
	public static function load(string $root, array $inject = []): static
	{
		$users = new static();

		foreach (Dir::read($root) as $userDirectory) {
			if (is_dir($root . '/' . $userDirectory) === false) {
				continue;
			}

			// get role information
			$path = $root . '/' . $userDirectory . '/index.php';
			if (is_file($path) === true) {
				$credentials = F::load($path, allowOutput: false);
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
	 * Shortcut for `$users->filter('role', 'admin')`
	 */
	public function role(string $role): static
	{
		return $this->filter('role', $role);
	}
}
