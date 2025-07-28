<?php

namespace Kirby\Panel;

use Closure;
use Kirby\Panel\Routes\DialogRoutes;
use Kirby\Panel\Routes\DrawerRoutes;
use Kirby\Panel\Routes\DropdownRoutes;
use Kirby\Panel\Routes\RequestRoutes;
use Kirby\Panel\Routes\SearchRoutes;
use Kirby\Panel\Routes\ViewRoutes;
use Kirby\Toolkit\I18n;

/**
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
class Area
{
	public function __construct(
		protected string $id,
		protected array $breadcrumb = [],
		protected Closure|array|string|null $breadcrumbLabel = null,
		protected array $buttons = [],
		protected array $dialogs = [],
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

		if ($value === null) {
			return null;
		}

		return I18n::translate($value, $value);
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
	 * Returns properties that are used to create
	 * the respective menu item for the area
	 */
	public function menuItem(): array
	{
		return [
			'id'     => $this->id(),
			'icon'   => $this->icon(),
			'label'  => $this->label(),
			'link'   => $this->link(),
			'menu'   => $this->menu(),
			'title'  => $this->title()
		];
	}

	/**
	 * Extract all routes for searches
	 */
	public function routes(): array
	{
		$viewRoutes     = new ViewRoutes($this, $this->views());
		$searchRoutes   = new SearchRoutes($this, $this->searches());
		$dialogRoutes   = new DialogRoutes($this, $this->dialogs());
		$drawerRoutes   = new DrawerRoutes($this, $this->drawers());
		$dropdownRoutes = new DropdownRoutes($this, $this->dropdowns());
		$requestRoutes  = new RequestRoutes($this, $this->requests());

		return [
			...$viewRoutes->toArray(),
			...$searchRoutes->toArray(),
			...$dialogRoutes->toArray(),
			...$drawerRoutes->toArray(),
			...$dropdownRoutes->toArray(),
			...$requestRoutes->toArray(),
		];
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
	 * Returns properties that will be added to the
	 * view response (one level above the props) to
	 * render the view component properly
	 */
	public function view(): array
	{
		return [
			'breadcrumb'      => $this->breadcrumb(),
			'breadcrumbLabel' => $this->breadcrumbLabel(),
			'icon'            => $this->icon(),
			'id'              => $this->id(),
			'label'           => $this->label(),
			'search'          => $this->search(),
			'title'           => $this->title(),
		];
	}
}
