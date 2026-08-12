<?php

namespace Kirby\Cms;

use Kirby\Filesystem\F;

/**
 * Extension of the Collection class that
 * introduces `Roles::factory()` to convert an
 * array of role definitions into a proper
 * collection with Role objects. It also has
 * a `Roles::load()` method that handles loading
 * role definitions from disk.
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 *
 * @extends LazyCollection<Role>
 */
class Roles extends LazyCollection
{
	/**
	 * All registered roles methods
	 */
	public static array $methods = [];

	/**
	 * Creates a new Collection with the given objects
	 *
	 * @param iterable<Role> $objects
	 * @param array $inject Props to inject into hydrated role objects
	 */
	public function __construct(
		iterable $objects = [],
		protected object|null $parent = null,
		protected array $inject = []
	) {
		parent::__construct($objects, $parent);
	}

	/**
	 * Returns a filtered list of all
	 * roles that can be changed by the
	 * current user
	 *
	 * Use with `$kirby->roles()`. For retrieving
	 * which roles are available for a specific user,
	 * use `$user->roles()` without additional filters.
	 *
	 * @return $this|static
	 * @throws \Exception
	 */
	public function canBeChanged(): static
	{
		if (App::instance()->user()?->isAdmin() !== true) {
			return $this->filter('isAccessible', true)->filter(function ($role) {
				$newUser = new User([
					'email' => 'test@getkirby.com',
					'role'  => $role->id()
				]);

				return $newUser->permissions()->can('changeRole');
			});
		}

		return $this;
	}

	/**
	 * Returns a filtered list of all
	 * roles that can be created by the
	 * current user.
	 *
	 * Use with `$kirby->roles()`.
	 *
	 * @return $this|static
	 * @throws \Exception
	 */
	public function canBeCreated(): static
	{
		if (App::instance()->user()?->isAdmin() !== true) {
			return $this->filter('isAccessible', true)->filter(function ($role) {
				$newUser = new User([
					'email' => 'test@getkirby.com',
					'role'  => $role->id()
				]);

				return $newUser->permissions()->can('create');
			});
		}

		return $this;
	}

	public static function factory(array $roles, array $inject = []): static
	{
		$collection = new static();

		// read all user blueprints
		foreach ($roles as $props) {
			$role = Role::factory($props, $inject);
			$collection->set($role->id(), $role);
		}

		// always include the admin role
		if ($collection->find('admin') === null) {
			$collection->set('admin', Role::defaultAdmin());
		}

		// return the collection sorted by name
		return $collection->sort('name', 'asc');
	}

	/**
	 * Loads the role from the blueprint file
	 * that was collected for the given key
	 */
	protected function hydrateElement(string $key): Role|null
	{
		$file = $this->hydration[$key] ?? null;

		if ($file === null) {
			return null;
		}

		return $this->data[$key] = Role::load($file, $this->inject);
	}

	/**
	 * The blueprint file is kept for each role individually,
	 * so only the injected props are shared by the collection
	 */
	protected function hydrationSource(): array
	{
		return $this->inject;
	}

	public static function load(
		string|null $root = null,
		array $inject = []
	): static {
		$kirby = App::instance();
		$roles = new static(inject: $inject);

		// load roles from plugins; their name (and therefore the
		// collection key) is only known once the blueprint has been
		// resolved, so they cannot be loaded lazily
		foreach ($kirby->extensions('blueprints') as $name => $blueprint) {
			if (str_starts_with($name, 'users/') === false) {
				continue;
			}

			// callback option can be return array or blueprint file path
			if (is_callable($blueprint) === true) {
				$blueprint = $blueprint($kirby);
			}

			$role = match (is_array($blueprint)) {
				true  => Role::factory($blueprint, $inject),
				false => Role::load($blueprint, $inject)
			};

			$roles->set($role->id(), $role);
		}

		// load roles from directory; `Role::load()` always takes the
		// name from the filename, so the collection key is known
		// without having to read and extend the blueprint
		if ($root !== null) {
			foreach (glob($root . '/*.yml') as $file) {
				$filename = basename($file);

				if ($filename === 'default.yml') {
					continue;
				}

				$roles->deferHydration(
					key:       F::name($file),
					hydration: $file
				);
			}
		}

		// always include the admin role
		if ($roles->has('admin') === false) {
			$roles->set('admin', Role::defaultAdmin($inject));
		}

		// the collection key is the role name, so the roles can
		// be sorted by name without having to load a single one
		ksort($roles->data, SORT_NATURAL | SORT_FLAG_CASE);

		return $roles;
	}
}
