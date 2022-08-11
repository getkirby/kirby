<?php

namespace Kirby\Cms;

/**
 * Extension of the Collection class that
 * introduces `Roles::factory()` to convert an
 * array of role definitions into a proper
 * collection with Role objects. It also has
 * a `Roles::load()` method that handles loading
 * role definitions from disk.
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class Roles extends Collection
{
	/**
	 * Returns a filtered list of all
	 * roles that can be created by the
	 * current user
	 *
	 * @return $this|static
	 * @throws \Exception
	 */
	public function canBeChanged()
	{
		if (App::instance()->user()) {
			return $this->filter(function ($role) {
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
	 * current user
	 *
	 * @return $this|static
	 * @throws \Exception
	 */
	public function canBeCreated()
	{
		if (App::instance()->user()) {
			return $this->filter(function ($role) {
				$newUser = new User([
					'email' => 'test@getkirby.com',
					'role'  => $role->id()
				]);

				return $newUser->permissions()->can('create');
			});
		}

		return $this;
	}

	/**
	 * @param array $roles
	 * @param array $inject
	 * @return static
	 */
	public static function factory(array $roles, array $inject = [])
	{
		$collection = new static();

		// read all user blueprints
		foreach ($roles as $props) {
			$role = Role::factory($props, $inject);
			$collection->set($role->id(), $role);
		}

		// always include the admin role
		if ($collection->find('admin') === null) {
			$collection->set('admin', Role::admin());
		}

		// return the collection sorted by name
		return $collection->sort('name', 'asc');
	}

	/**
	 * @param string|null $root
	 * @param array $inject
	 * @return static
	 */
	public static function load(string $root = null, array $inject = [])
	{
		$kirby = App::instance();
		$roles = new static();

		// load roles from plugins
		foreach ($kirby->extensions('blueprints') as $blueprintName => $blueprint) {
			if (substr($blueprintName, 0, 6) !== 'users/') {
				continue;
			}

			// callback option can be return array or blueprint file path
			if (is_callable($blueprint) === true) {
				$blueprint = $blueprint($kirby);
			}

			if (is_array($blueprint) === true) {
				$role = Role::factory($blueprint, $inject);
			} else {
				$role = Role::load($blueprint, $inject);
			}

			$roles->set($role->id(), $role);
		}

		// load roles from directory
		if ($root !== null) {
			foreach (glob($root . '/*.yml') as $file) {
				$filename = basename($file);

				if ($filename === 'default.yml') {
					continue;
				}

				$role = Role::load($file, $inject);
				$roles->set($role->id(), $role);
			}
		}

		// always include the admin role
		if ($roles->find('admin') === null) {
			$roles->set('admin', Role::admin($inject));
		}

		// return the collection sorted by name
		return $roles->sort('name', 'asc');
	}
}
