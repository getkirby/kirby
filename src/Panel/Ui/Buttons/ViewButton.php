<?php

namespace Kirby\Panel\Ui\Buttons;

use Closure;
use Kirby\Cms\App;
use Kirby\Panel\Panel;
use Kirby\Panel\Ui\Button;
use Kirby\Toolkit\Controller;

/**
 * A view button is a UI button, by default small in size and filles,
 * that optionally defines options for a dropdown
 *
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     5.0.0
 * @internal
 */
class ViewButton extends Button
{
	public function __construct(
		public string $component = 'k-view-button',
		public array|null $badge = null,
		public string|null $class = null,
		public string|bool|null $current = null,
		public string|null $dialog = null,
		public bool $disabled = false,
		public string|null $drawer = null,
		public bool|null $dropdown = null,
		public string|null $icon = null,
		public string|null $link = null,
		public array|string|null $options = null,
		public bool|string $responsive = true,
		public string|null $size = 'sm',
		public string|null $style = null,
		public string|null $target = null,
		public string|null $text = null,
		public string|null $theme = null,
		public string|null $title = null,
		public string $type = 'button',
		public string|null $variant = 'filled',
	) {
	}

	/**
	 * Creates new view button by looking up
	 * the button in all areas, if referenced by name
	 * and resolving to proper instance
	 */
	public static function factory(
		string|array|Closure $button,
		string|null $view = null,
		array $data = []
	): static|null {
		// referenced by name
		if (is_string($button) === true) {
			$button = static::find($button, $view);
		}

		$button = static::resolve($button, $data);

		if (
			$button === null ||
			$button instanceof ViewButton
		) {
			return $button;
		}

		return new static(...static::normalize($button));
	}

	/**
	 * Finds a view button by name
	 * among the defined buttons from all areas
	 */
	public static function find(
		string $name,
		string|null $view = null
	): array|Closure {
		// collect all buttons from areas
		$buttons = Panel::buttons();

		// try to find by full name (view-prefixed)
		if ($view && $button = $buttons[$view . '.' . $name] ?? null) {
			return $button;
		}

		// try to find by just name
		if ($button = $buttons[$name] ?? null) {
			return $button;
		}

		// assume it must be a custom view button component
		return ['component' => 'k-' . $name . '-view-button'];
	}

	/**
	 * Transforms an array to be used as
	 * named arguments in the constructor
	 * @internal
	 */
	public static function normalize(array $button): array
	{
		// if component and props are both not set, assume shortcut
		// where props were directly passed on top-level
		if (
			isset($button['component']) === false &&
			isset($button['props']) === false
		) {
			return $button;
		}

		// flatten array
		if ($props = $button['props'] ?? null) {
			$button = [...$props, ...$button];
			unset($button['props']);
		}

		return $button;
	}

	public function props(): array
	{
		return [
			...parent::props(),
			'options' => $this->options
		];
	}

	/**
	 * Transforms a closure to the actual view button
	 * by calling it with the provided arguments
	 * @internal
	 */
	public static function resolve(
		Closure|array $button,
		array $data = []
	): static|array|null {
		if ($button instanceof Closure) {
			$kirby      = App::instance();
			$controller = new Controller($button);
			$button     = $controller->call(data: [
				'kirby' => $kirby,
				'site'  => $kirby->site(),
				'user'  => $kirby->user(),
				...$data
			]);
		}

		return $button;
	}
}
