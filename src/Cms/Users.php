<?php

namespace Kirby\Cms;

use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\LogicException;
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
 * @extends \Kirby\Cms\LazyCollection<TUser>
 */
class Users extends LazyCollection
{
	use HasUuids;

	/**
	 * Creates a new Collection with the given objects
	 *
	 * @param iterable<TUser> $objects
	 * @param string|null $root Directory to dynamically load user
	 *                          objects from during hydration
	 * @param array $inject Props to inject into hydrated user objects
	 */
	public function __construct(
		iterable $objects = [],
		protected object|null $parent = null,
		protected string|null $root = null,
		protected array $inject = []
	) {
		parent::__construct($objects, $parent);
	}

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

		foreach ($this as $user) {
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
	 * Loads a user object, sets it in `$this->data[$key]`
	 * and returns the hydrated user object
	 */
	protected function hydrateElement(string $key): User|null
	{
		if ($this->root === null) {
			throw new LogicException('Cannot hydrate user "' . $key . '" with missing root'); // @codeCoverageIgnore
		}

		// check if the user directory exists if not all keys have been
		// populated in the collection, otherwise we can assume that
		// this method will only be called on "unhydrated" user IDs
		$root = $this->root . '/' . $key;
		if ($this->initialized === false && is_dir($root) === false) {
			return null;
		}

		// get role information
		$path = $root . '/index.php';
		if (is_file($path) === true) {
			$credentials = F::load($path, allowOutput: false);
		}

		// create user model based on role
		$user = User::factory([
			'id'          => $key,
			'model'       => $credentials['role'] ?? null,
			'credentials' => is_array($credentials ?? null) ? $credentials : null
		] + $this->inject);

		return $this->data[$key] = $user;
	}

	/**
	 * Ensures that the IDs for all valid users are loaded in the
	 * `$data` array and sets `$initialized` to `true` afterwards
	 */
	public function initialize(): void
	{
		// skip another initialization if it already has been initialized
		if ($this->initialized === true) {
			return;
		}

		if ($this->root === null) {
			throw new LogicException('Cannot initialize users with missing root'); // @codeCoverageIgnore
		}

		// ensure the order matches the filesystem, even if
		// individual users have been hydrated/added before
		$existing   = $this->data;
		$this->data = [];

		foreach (Dir::read($this->root) as $userDirectory) {
			if (is_dir($this->root . '/' . $userDirectory) === false) {
				continue;
			}

			$this->data[$userDirectory] = null;
		}

		$this->data = [...$this->data, ...$existing];

		$this->initialized = true;
	}

	/**
	 * Loads users from disk by passing the absolute directory path (root)
	 */
	public static function load(string $root, array $inject = []): static
	{
		$users = new static(root: $root, inject: $inject);
		$users->initialized = false;

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
