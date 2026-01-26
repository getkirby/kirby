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

	protected array $actions = [
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
			defaults: $this->actions,
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

	protected function normalize(
		array|bool|null $settings,
		array $defaults = [],
		array $aliases = []
	): array{
		$permissions = $defaults;
		$normalized  = $settings;

		// transform into wildcard
		if (is_bool($normalized) === true) {
			$normalized = ['*' => $normalized];
		}

		if (is_array($normalized) === false) {
			return $permissions;
		}

		// handle category wildcards
		if (array_key_exists('*', $normalized) === true) {
			$normalized += array_fill_keys(
				array_keys($defaults),
				$normalized['*']
			);

			unset($normalized['*']);
		}

		foreach ($normalized as $category => $actions) {
			// skip undefined categories
			if (isset($defaults[$category]) === false) {
				continue;
			}

			// transform into wildcard
			if (is_bool($actions) === true) {
				$actions = ['*' => $actions];
			}

			if (is_array($actions) === false) {
				continue;
			}

			// handle action wildcards
			if (array_key_exists('*', $actions) === true) {
				$actions += array_fill_keys(
					array_keys($defaults[$category]),
					$actions['*']
				);

				unset($actions['*']);
			}

			foreach ($actions as $action => $value) {
				$permissions[$category][$action] = boolval($value);
			}

			foreach ($permissions[$category] as $action => $value) {
				$alias = $aliases[$category][$action] ?? null;

				if ($alias !== null) {
					if (is_callable($alias) === true) {
						$alias = $alias($value);
					}

					if (is_array($alias) === false) {
						$alias = [$alias => $value];
					}

					foreach ($alias as $key => $value) {
						if (isset($settings[$category][$key]) === false) {
							$permissions[$category][$key] = boolval($value);
						}
					}
				}

				if (isset($defaults[$category][$action]) === false) {
					unset($permissions[$category][$action]);
				}
			}
		}

		return $permissions;
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

	public function toArray(): array
	{
		return $this->actions;
	}
}
