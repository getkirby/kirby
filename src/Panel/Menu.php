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
	public array $config;
	protected array $items;
	protected App $kirby;

	public function __construct(
		protected array $areas = [],
		protected array $permissions = [],
		protected string|null $current = null,
		array|null $config = null,
	) {
		$this->kirby = App::instance();

		$config ??= $this->kirby->option('panel.menu');

		if ($config instanceof Closure) {
			$config = $config($this->kirby);
		}

		$this->config = $config ?? $this->defaults();
	}

	/**
	 * Returns all Panel areas
	 * @since 6.0.0
	 */
	public function areas(): array
	{
		return $this->areas;
	}

	/**
	 * Returns the current menu item id
	 * @since 6.0.0
	 */
	public function current(): string|null
	{
		return $this->current;
	}

	/**
	 * Returns a default menu config as fallback
	 * @since 6.0.0
	 */
	public function defaults(): array
	{
		// ensure that some defaults are on top in the right order
		$defaults = ['site', 'languages', 'users', 'system'];

		// add all other areas after that
		$areas       = $this->areas();
		$additionals = array_diff(array_keys($areas), $defaults);
		return [...$defaults, ...$additionals];
	}

	public function hasPermission(string $id): bool
	{
		return $this->permissions()['access'][$id] ?? true;
	}

	/**
	 * Whether the menu entry should receive aria-current
	 */
	public function isCurrent(
		string $id,
		bool|Closure|null $callback = null,
	): bool {
		if ($callback !== null) {
			if ($callback instanceof Closure) {
				$callback = $callback($this->current());
			}

			return $callback;
		}

		return $this->current() === $id;
	}

	public function item(string $id, array $props = []): MenuItem|null
	{
		return MenuItem::for($this, $id, $props);
	}

	/**
	 * Returns all menu items
	 * @since 6.0.0
	 */
	public function items(): array
	{
		if (isset($this->items) === true) {
			return $this->items;
		}

		$items = [];

		// add all menu items from the config
		foreach ($this->config as $id => $config) {
			// [0 => '-']
			if ($config === '-') {
				$items[] = '-';
				continue;
			}

			// [0 => $id]
			// simple string id references global area definition
			if (is_numeric($id) === true) {
				$items[] = $this->item($config);
			}

			// [$id => true]
			if ($config === true) {
				$items[] = $this->item($id);
				continue;
			}

			// [$id => [ ... ] ]
			if (is_array($config) === true) {
				// force to be shown in the menu
				$items[] = $this->item($id, ['menu' => true, ...$config]);
			}
		}

		$items[] = '-';

		// add additional menu items
		$items[] = new MenuItem(
			id: 'changes',
			icon: 'edit-line',
			text: I18n::translate('changes'),
			dialog: 'changes'
		);

		$items[] = new MenuItem(
			id: 'account',
			icon: 'account',
			text: I18n::translate('view.account'),
			link: 'account',
			current: $this->current === 'account',
			disabled: $this->hasPermission('account') === false
		);

		$items[] = new MenuItem(
			id: 'logout',
			icon: 'logout',
			text: I18n::translate('logout'),
			link: 'logout'
		);

		return $this->items = array_filter($items);
	}

	/**
	 * Returns the permissions for the current user
	 * @since 6.0.0
	 */
	public function permissions(): array
	{
		return $this->permissions;
	}
}
