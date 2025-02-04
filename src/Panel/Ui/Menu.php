<?php

namespace Kirby\Panel\Ui;

use Closure;
use Kirby\Cms\App;
use Kirby\Panel\Area;
use Kirby\Toolkit\A;

/**
 * The Menu class takes care of gathering
 * all menu entries for the Panel
 * @since 4.0.0
 *
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class Menu extends Component
{
	protected array $areas = [];

	public function __construct(
		array $areas = [],
		protected array $permissions = [],
		protected string|null $current = null
	) {
		foreach ($areas as $area) {
			$this->areas[$area->id()] = $area;
		}

		parent::__construct(
			component: 'k-panel-menu'
		);
	}

	/**
	 * Helper to fetch an area instance and merge the given props
	 */
	public function area(string $id, array $props = []): Area|null
	{
		return ($this->areas[$id] ?? null)?->merge($props);
	}

	/**
	 * Returns all areas that are configured for the menu
	 * @internal
	 */
	public function areas(): array
	{
		$areas = [];

		foreach ($this->config() as $id => $area) {
			// [0 => '-']
			if ($area === '-') {
				$areas[] = '-';
				continue;
			}

			// [$areaId => Ui() ]
			if ($area instanceof Component) {
				$areas[] = $area;
				continue;
			}

			// [0 => $areaId]
			if (is_numeric($id) === true) {
				$areas[] = $this->area($area);
				continue;
			}

			// [$areaId => true]
			if ($area === true) {
				$areas[] = $this->area($id);
				continue;
			}

			// [$areaId => [ ... ] ]
			if (is_array($area) === true) {
				// show the area in the
				// menu by default
				$props = [
					'menu' => true,
					...$area
				];

				// merge the props with an existing area or create a new custom one
				$areas[] = $this->area($id, $props) ?? new Area($id, ...$props);
			}
		}

		return array_values(array_filter($areas));
	}

	/**
	 * Loads the custom menu config
	 * and merges it with the default areas
	 * @internal
	 */
	public function config(): array
	{
		// get from config option which areas should be listed in the menu
		$kirby = App::instance();
		$items = $kirby->option('panel.menu');

		// lazy-load items
		if ($items instanceof Closure) {
			$items = $items($kirby);
		}

		// if no config is defined…
		if ($items === null) {
			// ensure that some defaults are on top in the right order
			$default    = ['site', 'languages', 'users', 'system'];
			// add all other areas after that
			$additional = array_diff(array_keys($this->areas), $default);
			$items      = [...$default, ...$additional];
		}

		return $items;
	}

	/**
	 * Transforms an area definition into a menu entry
	 * @internal
	 */
	public function item(Area|null $area): MenuItem|null
	{
		if ($area === null) {
			return null;
		}

		return $area->menuItem(
			areas:       $this->areas,
			permissions: $this->permissions,
			current:     $this->current
		);
	}

	/**
	 * Returns all menu items
	 */
	public function items(): array
	{
		$items = A::map(
			$this->areas(),
			function (Area|Component|string $area) {
				if ($area === '-') {
					return '-';
				}

				if ($area instanceof Component) {
					return $area->render();
				}

				if ($item = $this->item($area)) {
					return $item->render();
				}
			}
		);

		return array_filter([
			...$items,
			'-',
			...$this->options()
		]);
	}

	/**
	 * Default options entries for bottom of menu
	 * @internal
	 */
	public function options(): array
	{
		$changes = new MenuItem(
			icon:   'edit-line',
			dialog: 'changes',
			text:   'changes'
		);

		$account = new MenuItem(
			current: $this->current === 'account',
			disabled: ($this->permissions['access']['account'] ?? true) === false,
			icon: 'account',
			link: 'account',
			text: 'view.account'
		);

		$logout = new MenuItem(
			icon: 'logout',
			link: 'logout',
			text: 'logout'
		);

		return [
			$changes->render(),
			$account->render(),
			$logout->render(),
		];
	}

	public function props(): array
	{
		return [
			...parent::props(),
			'items' => $this->items()
		];
	}
}
