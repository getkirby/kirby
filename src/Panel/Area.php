<?php

namespace Kirby\Panel;

use Closure;
use Kirby\Panel\Ui\MenuItem;
use Kirby\Toolkit\I18n;

/**
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     5.0.0
 */
class Area
{
	public function __construct(
		protected string $id,
		protected array $breadcrumb = [],
		protected Closure|array|string|null $breadcrumbLabel = null,
		protected array $buttons = [],
		protected Closure|bool|null $current = null,
		protected string|null $dialog = null,
		protected array $dialogs = [],
		protected string|null $drawer = null,
		protected array $drawers = [],
		protected array $dropdowns = [],
		protected string|null $icon = null,
		protected Closure|array|string|null $label = null,
		protected string|null $link = null,
		protected Closure|array|bool|string $menu = false,
		protected string|null $search = null,
		protected array $searches = [],
		protected array $requests = [],
		protected Closure|array|string|null $title = null,
		protected array $views = [],
	) {
	}

	public function __call(string $name, array $args = [])
	{
		return $this->{$name};
	}

	/**
	 * A custom breadcrumb label that will be used for the
	 * breadcrumb instead of the default label
	 */
	public function breadcrumbLabel(): string
	{
		return $this->i18n($this->breadcrumbLabel ?? $this->label());
	}

	/**
	 * Translator for breadcrumbLabel, label & title
	 */
	protected function i18n(Closure|array|string|null $value): string|null
	{
		if ($value instanceof Closure) {
			$value = $value();
		}

		return I18n::translate($value, $value);
	}

	/**
	 * Checks for access permissions for this area
	 */
	public function isAccessible(array $permissions): bool
	{
		return ($permissions['access'][$this->id] ?? true) === true;
	}

	/**
	 * Checks if the area is currently active
	 */
	public function isCurrent(string|null $current = null): bool
	{
		if ($this->current === null) {
			return $this->id === $current;
		}

		if ($this->current instanceof Closure) {
			return ($this->current)($current);
		}

		return $this->current;
	}

	/**
	 * The label is used for the menu item and the breadcrumb
	 * unless a custom breadcrumb label is defined
	 */
	public function label(): string
	{
		return $this->i18n($this->label ?? $this->id);
	}

	/**
	 * Link for the menu item
	 */
	public function link(): string
	{
		return $this->link ?? $this->id;
	}

	/**
	 * Set or overwrite additional props via array
	 */
	public function merge(array $props): static
	{
		foreach ($props as $key => $value) {
			$this->{$key} = $value;
		}

		return $this;
	}

	/**
	 * Evaluate the menu settings to determine
	 * how the menu item for the area should be rendered
	 * and if it should be rendered at all
	 */
	public function menuItem(
		array $areas = [],
		array $permissions = [],
		string|null $current = null
	): MenuItem|null {
		// areas without access permissions get skipped entirely
		if ($this->isAccessible($permissions) === false) {
			return null;
		}

		$menu = $this->menu;

		// menu setting can be a callback
		// that returns true, false or 'disabled'
		if ($menu instanceof Closure) {
			$menu = $menu($areas, $permissions, $current);
		}

		// false will remove the area/entry entirely
		// just like with disabled permissions
		if ($menu === false) {
			return null;
		}

		// create a new menu item instance for the area
		$item = new MenuItem(
			current: $this->isCurrent($current),
			icon: $this->icon() ?? $this->id(),
			text: $this->label(),
			dialog: $this->dialog(),
			drawer: $this->drawer(),
			link: $this->link(),
		);

		// add the custom menu settings
		$item->merge(match ($menu) {
			'disabled' => ['disabled' => true],
			true       => [],
			default    => $menu
		});

		return $item;
	}

	/**
	 * The title is used for the browser title. It will fall back
	 * to the label if no custom title is defined.
	 */
	public function title(): string
	{
		return $this->i18n($this->title ?? $this->label());
	}

	/**
	 * Returns parameters that will be added to the
	 * view response (one level above the props) to
	 * render the view component properly
	 */
	public function toView(): array
	{
		return [
			'breadcrumb'      => $this->breadcrumb(),
			'breadcrumbLabel' => $this->breadcrumbLabel(),
			'icon'            => $this->icon(),
			'id'              => $this->id(),
			'label'           => $this->label(),
			'link'            => $this->link(),
			'search'          => $this->search(),
			'title'           => $this->title(),
		];
	}
}
