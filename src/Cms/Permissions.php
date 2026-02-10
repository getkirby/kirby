<?php

namespace Kirby\Cms;

use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\LogicException;
use Throwable;

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
			'access'     	 => true,
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
			'access'     	 => true,
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
		$update = static fn ($value) => [
			'edit' => $value,
			'save' => $value,
		];

		// normalize core actions
		$this->actions = $this->normalize(
			settings: $settings,
			defaults: $this->defaults,
			aliases: [
				'files' => ['update' => $update],
				'pages' => ['update' => $update],
				'site'  => ['update' => $update],
				'users' => ['update' => $update],
				'user'  => ['update' => $update],
			]
		);

		// dynamically register the extended actions
		foreach (static::$extendedActions as $key => $actions) {
			if (isset($this->actions[$key]) === true) {
				throw new InvalidArgumentException(
					message: 'The action ' . $key . ' is already a core action'
				);
			}

			$this->actions[$key] = $actions;
		}
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

	public function for(
		string $category,
		string|null $action = null,
		bool $default = false
	): bool {
		try {
			$permission = is_string($action)
				? $this->actions[$category][$action]
				: $this->actions[$category];
		} catch (Throwable) {
			return $default;
		}

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
					$defaults[$category][$key] = (bool)$value;
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
