<?php

namespace Kirby\Panel;

use Closure;
use Kirby\Cms\App;
use Kirby\Toolkit\I18n;

/**
 * The Menu class takes care of gathering
 * all menu entries for the Panel
 *
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     4.0.0
 * @unstable
 */
class Menu
{
	public function __construct(
		protected array $areas = [],
		protected array $permissions = [],
		protected string|null $current = null
	) {
	}

	/**
	 * Returns all areas that are configured for the menu
	 */
	public function areas(): array
	{
		// get from config option which areas should be listed in the menu
		$kirby = App::instance();
		$areas = $kirby->option('panel.menu');

		if ($areas instanceof Closure) {
			$areas = $areas($kirby);
		}

		// if no config is defined…
		if ($areas === null) {
			// ensure that some defaults are on top in the right order
			$defaults    = ['site', 'languages', 'users', 'system'];
			// add all other areas after that
			$additionals = array_diff(array_keys($this->areas), $defaults);
			$areas       = [...$defaults, ...$additionals];
		}

		$result = [];

		foreach ($areas as $id => $area) {
			// separator, keep as is in array
			if ($area === '-') {
				$result[] = '-';
				continue;
			}

			// for a simple id, get global area definition
			if (is_numeric($id) === true) {
				$id   = $area;
				$area = $this->areas[$id] ?? null;
			}

			// did not receive custom entry definition in config,
			// but also is not a global area
			if ($area === null) {
				continue;
			}

			// merge area definition (e.g. from config)
			// with global area definition
			if (is_array($area) === true) {
				$area = Panel::area($id, [
					...$this->areas[$id] ?? [],
					'menu' => true,
					...$area
				]);
			}

			$result[] = $area;
		}

		return $result;
	}

	/**
	 * Transforms an area definition into a menu entry
	 */
	public function entry(array $area): array|false
	{
		// areas without access permissions get skipped entirely
		if ($this->hasPermission($area['id']) === false) {
			return false;
		}

		// check menu setting from the area definition
		$menu = $area['menu'] ?? false;

		// menu setting can be a callback
		// that returns true, false or 'disabled'
		if ($menu instanceof Closure) {
			$menu = $menu($this->areas, $this->permissions, $this->current);
		}

		// false will remove the area/entry entirely
		//just like with disabled permissions
		if ($menu === false) {
			return false;
		}

		$menu = match ($menu) {
			'disabled' => ['disabled' => true],
			true       => [],
			default    => $menu
		};

		$entry = [
			'current'  => $this->isCurrent(
				$area['id'],
				$area['current'] ?? null
			),
			'icon'     => $area['icon'] ?? null,
			'link'     => $area['link'] ?? null,
			'dialog'   => $area['dialog'] ?? null,
			'drawer'   => $area['drawer'] ?? null,
			'target'   => $area['target'] ?? null,
			'text'     => I18n::translate($area['label'], $area['label']),
			'title'    => I18n::translate($area['title'] ?? null, $area['title'] ?? null),
			...$menu
		];

		// unset the link (which is always added by default to an area)
		// if a dialog or drawer should be opened instead
		if (isset($entry['dialog']) || isset($entry['drawer'])) {
			unset($entry['link']);
		}

		return array_filter($entry);
	}

	/**
	 * Returns all menu entries
	 */
	public function entries(): array
	{
		$entries = [];
		$areas   = $this->areas();

		foreach ($areas as $area) {
			if ($area === '-') {
				$entries[] = '-';
			} elseif ($entry = $this->entry($area)) {
				$entries[] = $entry;
			}
		}

		$entries[] = '-';

		return [...$entries, ...$this->options()];
	}

	/**
	 * Checks if the access permission to a specific area is granted.
	 * Defaults to allow access.
	 */
	public function hasPermission(string $id): bool
	{
		return $this->permissions['access'][$id] ?? true;
	}

	/**
	 * Whether the menu entry should receive aria-current
	 */
	public function isCurrent(
		string $id,
		bool|Closure|null $callback = null
	): bool {
		if ($callback !== null) {
			if ($callback instanceof Closure) {
				$callback = $callback($this->current);
			}

			return $callback;
		}

		return $this->current === $id;
	}

	/**
	 * Default options entries for bottom of menu
	 */
	public function options(): array
	{
		$options = [
			[
				'icon'     => 'edit-line',
				'dialog'   => 'changes',
				'text'     => I18n::translate('changes'),
			],
			[
				'current'  => $this->isCurrent('account'),
				'icon'     => 'account',
				'link'     => 'account',
				'disabled' => $this->hasPermission('account') === false,
				'text'     => I18n::translate('view.account'),
			],
			[
				'icon' => 'logout',
				'link' => 'logout',
				'text' => I18n::translate('logout')
			]
		];

		return $options;
	}
}
