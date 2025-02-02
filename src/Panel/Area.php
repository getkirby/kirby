<?php

namespace Kirby\Panel;

use Closure;
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
		protected array|string|null $label = null,
		protected string|null $link = null,
		protected Closure|array|bool|string $menu = false,
		protected string|null $search = null,
		protected array $searches = [],
		protected array $requests = [],
		protected array|string|null $title = null,
		protected array $views = [],
	) {
	}

	public function breadcrumb(): array
	{
		return $this->breadcrumb;
	}

	public function breadcrumbLabel(): string
	{
		$label = $this->breadcrumbLabel ?? $this->label();

		if ($label instanceof Closure) {
			$label = $label();
		}

		return I18n::translate($label, $label);
	}

	public function buttons(): array
	{
		return $this->buttons;
	}

	public function current(): null
	{
		return $this->current;
	}

	public function dialog(): string|null
	{
		return $this->dialog;
	}

	public function dialogs(): array
	{
		return $this->dialogs;
	}

	public function drawer(): string|null
	{
		return $this->drawer;
	}

	public function drawers(): array
	{
		return $this->drawers;
	}

	public function dropdowns(): array
	{
		return $this->dropdowns;
	}

	public function icon(): string|null
	{
		return $this->icon;
	}

	public function id(): string
	{
		return $this->id;
	}

	public function isAccessible(array $permissions): bool
	{
		return ($permissions['access'][$this->id] ?? true) === true;
	}

	public function isCurrent(string|null $current): bool
	{
		if ($this->current === null) {
			return $this->id === $current;
		}

		if ($this->current instanceof Closure) {
			return ($this->current)($current);
		}

		return $this->current;
	}

	public function label(): string
	{
		$label = $this->label ?? $this->id;
		return I18n::translate($label, $label);
	}

	public function link(): string
	{
		return $this->link ?? $this->id;
	}

	public function merge(array $props): static
	{
		foreach ($props as $key => $value) {
			$this->{$key} = $value;
		}

		return $this;
	}

	public function menu():	Closure|array|bool|string
	{
		return $this->menu;
	}

	public function menuSettings(
		array $areas,
		array $permissions,
		string|null $current
	): array|false {
		// areas without access permissions get skipped entirely
		if ($this->isAccessible($permissions) === false) {
			return false;
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
			return false;
		}

		return match ($menu) {
			'disabled' => ['disabled' => true],
			true       => [],
			default    => $menu
		};
	}

	public function requests(): array
	{
		return $this->requests;
	}

	public function search(): string|null
	{
		return $this->search;
	}

	public function searches(): array
	{
		return $this->searches;
	}

	public function title(): string
	{
		$title = $this->title ?? $this->label();
		return I18n::translate($title, $title);
	}

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

	public function views(): array
	{
		return $this->views;
	}
}
