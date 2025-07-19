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
 * @since     6.0.0
 * @unstable
 */
class MenuItem
{
	public function __construct(
		public string $id,
		public string|null $icon = null,
		public string|null $text = null,
		public string|null $link = null,
		public string|null $dialog = null,
		public string|null $drawer = null,
		public string|null $target = null,
		public string|null $title = null,
		public bool|null $current = false,
		public bool $disabled = false
	) {
		// unset the link (which is always added by default to an area)
		// if a dialog or drawer should be opened instead
		if ($this->dialog !== null || $this->drawer !== null) {
			$this->link = null;
		}
	}

	public static function for(
		Menu $parent,
		string $id,
		array $props
	): static|null {
		// Check if the user has access to the area
		if ($parent->hasPermission($id) === false) {
			return null;
		}

		$areas = $parent->areas();
		$area  = $areas[$id] ?? [];

		// Check menu setting:
		// menu setting can be a callback
		// that returns true, false or 'disabled'
		$menu = $props['menu'] ?? $area['menu'] ?? false;

		if ($menu instanceof Closure) {
			$menu = $menu($areas, $parent->permissions(), $parent->current());
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

		return new static(
			id: $id,
			icon: $props['icon'] ?? null,
			text: $props['text'] ?? $props['label'] ?? null,
			link: $props['link'] ?? null,
			dialog: $props['dialog'] ?? null,
			drawer: $props['drawer'] ?? null,
			target: $props['target'] ?? null,
			current: $parent->isCurrent(
				$id,
				$props['current'] ?? null
			),
			disabled: $props['disabled'] ?? false
		);
	}

	protected function i18n(string|null $key): string|null
	{
		return $key !== null ? I18n::translate($key, $key) : null;
	}

	public function toArray(): array
	{
		return [
			'current'  => $this->current,
			'disabled' => $this->disabled,
			'icon'     => $this->icon,
			'link'     => $this->link,
			'dialog'   => $this->dialog,
			'drawer'   => $this->drawer,
			'target'   => $this->target,
			'text'     => $this->i18n($this->text),
			'title'    => $this->i18n($this->title)
		];
	}
}
