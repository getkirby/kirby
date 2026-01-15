<?php

namespace Kirby\Cms;

use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\LogicException;

/**
 * Handles permission definition in each user
 * blueprint and wraps a couple useful methods
 * around it to check for available permissions.
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class Permissions
{
	public static array $extendedActions = [];

	protected array $defaults = [
		'access' => [
			'account'   => true,
			'languages' => true,
			'panel'     => true,
			'site'      => true,
			'system'    => true,
			'users'     => true
		],
		'files' => [
			'access'         => true,
			'changeName'     => true,
			'changeTemplate' => true,
			'create'         => true,
			'delete'         => true,
			'edit'           => true,
			'list'           => true,
			'read'           => true,
			'replace'        => true,
			'save'           => true,
			'sort'           => true
		],
		'languages' => [
			'create' => true,
			'delete' => true,
			'update' => true
		],
		'pages' => [
			'access'         => true,
			'changeSlug'     => true,
			'changeStatus'   => true,
			'changeTemplate' => true,
			'changeTitle'    => true,
			'create'         => true,
			'delete'         => true,
			'duplicate'      => true,
			'edit'           => true,
			'list'           => true,
			'move'           => true,
			'preview'        => true,
			'read'           => true,
			'save'           => true,
			'sort'           => true
		],
		'site' => [
			'changeTitle' => true,
			'edit'        => true,
			'save'        => true

		],
		'users' => [
			'changeEmail'    => true,
			'changeLanguage' => true,
			'changeName'     => true,
			'changePassword' => true,
			'changeRole'     => true,
			'create'         => true,
			'delete'         => true,
			'edit'		     => true,
			'save'		     => true
		],
		'user' => [
			'changeEmail'    => true,
			'changeLanguage' => true,
			'changeName'     => true,
			'changePassword' => true,
			'changeRole'     => true,
			'delete'         => true,
			'edit'		     => true,
			'save'		     => true
		]
	];

	protected array $actions;

	/**
	 * Permissions constructor
	 *
	 * @throws \Kirby\Exception\InvalidArgumentException
	 */
	public function __construct(array|bool|null $settings = [])
	{
		$defaults = $this->defaults;

		$update = static fn ($value) => [
			'edit' => $value,
			'save' => $value,
		];

		// dynamically register the extended actions
		foreach (static::$extendedActions as $key => $actions) {
			if (isset($defaults[$key]) === true) {
				throw new InvalidArgumentException(
					message: 'The action ' . $key . ' is already a core action'
				);
			}

			$defaults[$key] = $actions;
		}

		$this->actions = $this->normalize(
			settings: $settings,
			defaults: $this->actions,
			aliases: [
				'files' => ['update' => $update],
				'pages' => ['update' => $update],
				'site'  => ['update' => $update],
				'users' => ['update' => $update],
				'user'  => ['update' => $update],
			]
		);
	}

	protected function alias(
		array $actions,
		array $aliases,
		array|bool|null $defaults = []
	): array {
		foreach ($actions as $action => $value) {
			$alias = $aliases[$action] ?? null;

			if ($alias === null) {
				continue;
			}

			if (is_callable($alias) === true) {
				$alias = $alias($value);
			}

			if (is_array($alias) === false) {
				$alias = [$alias => $value];
			}

			foreach ($alias as $key => $value) {
				if (isset($defaults[$key]) === false) {
					$actions[$key] = (bool)$value;
				}
			}
		}

		return $actions;
	}

	/**
	 * Expands a bool or null shorthand into a full actions array
	 */
	protected function expand(
		array|bool|null $values,
		array $defaults = []
	): array {
		if (is_bool($values) === true) {
			$values = ['*' => $values];
		}

		if (is_array($values) === false) {
			return [];
		}

		if (array_key_exists('*', $values) === true) {
			$values += array_fill_keys(
				array_keys($defaults),
				$values['*']
			);

			unset($values['*']);
		}

		return $values;
	}

	/**
	 * @todo Replace first param with `string $category` in v6
	 */
	public function for(
		string|null $category = null,
		string|null $action = null,
		bool $default = false
	): bool {
		if (is_null($category) === true) {
			Helpers::deprecated(
				'Passing `$category = null` to `Permissions::for()` is not supported',
				'permissions-for-category-null'
			);

			return $default;
		}

		if ($this->has($category, $action) === false) {
			return $default;
		}

		$permission = $this->get($category, $action);

		if (is_bool($permission) === false) {
			$key = is_string($action) === true
				? $category . '.' . $action
				: $category;

			throw new LogicException(
				message: 'The value for the permission "' . $key . '" must be of type bool, ' . gettype($permission) . ' given'
			);
		}

		return $permission;
	}

	/**
	 * Returns the permission value for a category or a specific action
	 */
	protected function get(string $category, string|null $action = null): mixed
	{
		if (is_string($action) === true) {
			return $this->actions[$category][$action];
		}

		return $this->actions[$category];
	}

	/**
	 * Checks whether a category or specific action exists in the actions array
	 */
	protected function has(string $category, string|null $action = null): bool
	{
		if (is_string($action) === true) {
			return isset($this->actions[$category][$action]);
		}

		return isset($this->actions[$category]);
	}

	/**
	 * Normalizes the permission settings against the defaults
	 */
	protected function normalize(
		array|bool|null $settings,
		array $defaults = [],
		array $aliases = []
	): array {
		$categories = $this->expand($settings, $defaults);

		foreach ($categories as $category => $actions) {
			if (isset($defaults[$category]) === false) {
				continue;
			}

			$actions = $this->expand($actions, $defaults[$category]);

			if (isset($aliases[$category]) === true) {
				$actions = $this->alias(
					$actions,
					$aliases[$category],
					$settings[$category] ?? []
				);
			}

			foreach ($actions as $key => $value) {
				if (isset($defaults[$category][$key]) === true) {
					if (is_bool($value) === false) {
						throw new LogicException(
							message: 'The value for the permission "' . $category . '.' . $key . '" must be of type bool, ' . gettype($value) . ' given'
						);
					}

					$defaults[$category][$key] = $value;
				}
			}
		}

		return $defaults;
	}

	public function toArray(): array
	{
		return $this->actions;
	}
}
