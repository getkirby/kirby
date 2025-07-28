<?php

namespace Kirby\Panel;

use Closure;
use Kirby\Cms\App;
use Kirby\Panel\Ui\Button;
use Kirby\Panel\Ui\Component;
use Kirby\Toolkit\A;
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
	public Areas $areas;
	public array $config;
	protected array $items;
	protected App $kirby;

	public function __construct(
		Areas|null $areas = null,
		array|null $config = null,
		public array $permissions = [],
		public string|null $current = null
	) {
		$this->kirby = App::instance();

		// If no areas are provided, use the areas from the Panel object
		$this->areas = $areas ?? $this->kirby->panel()->areas();

		// If no config is provided, use the global config
		$config ??= $this->kirby->option('panel.menu');

		// If the config is a callback, call it with the App object
		if ($config instanceof Closure) {
			$config = $config($this->kirby);
		}

		// Use the defaults as fallback
		$this->config = $config ?? $this->defaults();
	}

	/**
	 * Returns a default menu config as fallback
	 * @since 6.0.0
	 */
	public function defaults(): array
	{
		$areas = $this->areas->keys();

		// ensure that some defaults are on top in the right order
		// (but make sure the areas are actually available)
		$defaults = array_filter(
			['site', 'languages', 'users', 'system'],
			fn ($id) => in_array($id, $areas, true)
		);

		// add all other areas after that
		return [...$defaults, ...array_diff($areas, $defaults)];
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
	 * Returns a menu item object for the given props
	 * @since 6.0.0
	 */
	public function item(string $id, array $props = []): Button|null
	{
		// Check if the user has access to the area
		if ($this->hasPermission($id) === false) {
			return null;
		}

		// If item is derived from an area, get the relevant properties
		$area = $this->areas->get($id)?->menuItem() ?? [];

		// Check menu setting:
		// menu setting can be a callback
		// that returns true, false or 'disabled'
		$menu = $props['menu'] ?? $area['menu'] ?? false;

		if ($menu instanceof Closure) {
			$menu = $menu($this->areas, $this->permissions, $this->current);
		}

		// false will remove the menu item entirely
		if ($menu === false) {
			return null;
		}

		$menu = match ($menu) {
			'disabled' => ['disabled' => true],
			true       => [],
			default    => $menu
		};

		$props = [...$area, ...$menu, ...$props];

		// if neither link, dialog or drawer are set, use id as fallback
		if (
			($props['dialog'] ?? null) === null &&
			($props['drawer'] ?? null) === null
		) {
			$props['link'] ??= $id;
		}

		return new Button(
			id: $id,
			current: $this->isCurrent(
				$id,
				$props['current'] ?? null
			),
			dialog: $props['dialog'] ?? null,
			disabled: $props['disabled'] ?? false,
			drawer: $props['drawer'] ?? null,
			icon: $props['icon'] ?? null,
			link: $props['link'] ?? null,
			target: $props['target'] ?? null,
			text: $text = $props['text'] ?? $props['label'] ?? null,
			title: $props['title'] ?? $text,
		);
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
				continue;
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
				continue;
			}

			// [$id => UiComponent() ]
			if ($config instanceof Component) {
				$items[] = $config;
				continue;
			}
		}

		$items[] = '-';

		// add additional menu items
		$items[] = new Button(
			id: 'changes',
			icon: 'edit-line',
			text: I18n::translate('changes'),
			dialog: 'changes'
		);

		$items[] = new Button(
			id: 'account',
			icon: 'account',
			text: I18n::translate('view.account'),
			link: 'account',
			current: $this->current === 'account',
			disabled: $this->hasPermission('account') === false
		);

		$items[] = new Button(
			id: 'logout',
			icon: 'logout',
			text: I18n::translate('logout'),
			link: 'logout'
		);

		return $this->items = array_values(array_filter($items));
	}

	/**
	 * Returns an array of all rendered menu items (component-props arrays)
	 */
	public function render(): array
	{
		return A::map(
			$this->items(),
			fn (Component|string $item) => match ($item) {
				'-'     => $item,
				default => $item->render()
			}
		);
	}
}
