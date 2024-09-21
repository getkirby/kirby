<?php

namespace Kirby\Cms;

use Kirby\Exception\InvalidArgumentException;

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
			'users'     => true,
		],
		'files' => [
			'access'     	 => true,
			'changeName'     => true,
			'changeTemplate' => true,
			'create'         => true,
			'delete'         => true,
			'list'           => true,
			'read'           => true,
			'replace'        => true,
			'sort'           => true,
			'update'         => true
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
			'list'           => true,
			'move'           => true,
			'preview'        => true,
			'read'           => true,
			'sort'           => true,
			'update'         => true
		],
		'site' => [
			'changeTitle' => true,
			'update'      => true
		],
		'users' => [
			'changeEmail'    => true,
			'changeLanguage' => true,
			'changeName'     => true,
			'changePassword' => true,
			'changeRole'     => true,
			'create'         => true,
			'delete'         => true,
			'update'         => true
		],
		'user' => [
			'changeEmail'    => true,
			'changeLanguage' => true,
			'changeName'     => true,
			'changePassword' => true,
			'changeRole'     => true,
			'delete'         => true,
			'update'         => true
		]
	];

	/**
	 * Permissions constructor
	 *
	 * @throws \Kirby\Exception\InvalidArgumentException
	 */
	public function __construct(array|bool|null $settings = [])
	{
		// dynamically register the extended actions
		foreach (static::$extendedActions as $key => $actions) {
			if (isset($this->actions[$key]) === true) {
				throw new InvalidArgumentException(
					message: 'The action ' . $key . ' is already a core action'
				);
			}

			$this->actions[$key] = $actions;
		}

		if (is_array($settings) === true) {
			return $this->setCategories($settings);
		}

		if (is_bool($settings) === true) {
			return $this->setAll($settings);
		}
	}

	public function for(
		string|null $category = null,
		string|null $action = null,
		bool $default = false
	): bool {
		if ($action === null) {
			if ($this->hasCategory($category) === false) {
				return $default;
			}

			return $this->actions[$category];
		}

		if ($this->hasAction($category, $action) === false) {
			return $default;
		}

		return $this->actions[$category][$action];
	}

	protected function hasAction(string $category, string $action): bool
	{
		return
			$this->hasCategory($category) === true &&
			array_key_exists($action, $this->actions[$category]) === true;
	}

	protected function hasCategory(string $category): bool
	{
		return array_key_exists($category, $this->actions) === true;
	}

	/**
	 * @return $this
	 */
	protected function setAction(
		string $category,
		string $action,
		$setting
	): static {
		// wildcard to overwrite the entire category
		if ($action === '*') {
			return $this->setCategory($category, $setting);
		}

		$this->actions[$category][$action] = $setting;

		return $this;
	}

	/**
	 * @return $this
	 */
	protected function setAll(bool $setting): static
	{
		foreach ($this->actions as $categoryName => $actions) {
			$this->setCategory($categoryName, $setting);
		}

		return $this;
	}

	/**
	 * @return $this
	 */
	protected function setCategories(array $settings): static
	{
		foreach ($settings as $name => $actions) {
			if (is_bool($actions) === true) {
				$this->setCategory($name, $actions);
			}

			if (is_array($actions) === true) {
				foreach ($actions as $action => $setting) {
					$this->setAction($name, $action, $setting);
				}
			}
		}

		return $this;
	}

	/**
	 * @return $this
	 * @throws \Kirby\Exception\InvalidArgumentException
	 */
	protected function setCategory(string $category, bool $setting): static
	{
		if ($this->hasCategory($category) === false) {
			throw new InvalidArgumentException(
				message: 'Invalid permissions category'
			);
		}

		foreach ($this->actions[$category] as $action => $actionSetting) {
			$this->actions[$category][$action] = $setting;
		}

		return $this;
	}

	public function toArray(): array
	{
		return $this->actions;
	}
}
