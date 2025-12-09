<?php

namespace Kirby\Panel\Ui\Buttons;

use Closure;
use Kirby\Cms\App;
use Kirby\Cms\Language;
use Kirby\Cms\ModelWithContent;
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
 */
class ViewButton extends Button
{
	public function __construct(
		public string $component = 'k-view-button',
		public readonly ModelWithContent|Language|null $model = null,
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
		public string|array|null $text = null,
		public string|null $theme = null,
		public string|array|null $title = null,
		public string $type = 'button',
		public string|null $variant = 'filled',
		...$attrs
	) {
		$this->attrs = $attrs;
	}

	/**
	 * Creates new view button by looking up
	 * the button in all areas, if referenced by name
	 * and resolving to proper instance
	 */
	public static function factory(
		string|array|Closure|bool $button = true,
		string|int|null $name = null,
		string|null $view = null,
		ModelWithContent|Language|null $model = null,
		array $data = []
	): static|null {
		// if referenced by name (`name: false`),
		// don't render anything
		if ($button === false) {
			return null;
		}

		// transform `- name` notation to `name: true`
		if (
			is_string($name) === false &&
			is_string($button) === true
		) {
			$name   = $button;
			$button = true;
		}

		// if referenced by name (`name: true`),
		// try to get button definition from areas or config
		if ($button === true) {
			$button = static::find($name, $view);
		}

		// resolve Closure to button object or array
		if ($button instanceof Closure) {
			$button = static::resolve($button, $model, $data);
		}

		if (
			$button === null ||
			$button instanceof ViewButton
		) {
			return $button;
		}

		// flatten array into list of arguments for this class
		$button = static::normalize($button);

		// if button definition has a name, use it for the component name
		if (is_string($name) === true) {
			// if this specific component does not exist,
			// `k-view-buttons` will fall back to `k-view-button` again
			$button['component'] ??= 'k-' . $name . '-view-button';
		}

		return new static(...$button, model: $model);
	}

	/**
	 * Finds a view button by name
	 * among the defined buttons from all areas
	 * @unstable
	 */
	public static function find(
		string $name,
		string|null $view = null
	): array|Closure {
		// collect all buttons from areas and config
		$buttons = [
			...Panel::buttons(),
			...App::instance()->option('panel.viewButtons.' . $view, [])
		];

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
	 * @unstable
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
		// helper for props that support Kirby queries
		$resolve = fn (string|null $value) =>
			$value ?
			$this->model?->toSafeString($value) ?? $value :
			null;

		$options = $this->options;

		if (is_string($options) === true) {
			$options = $resolve($options);
		}

		return [
			...$props = parent::props(),
			'dialog'  => $resolve($props['dialog']),
			'drawer'  => $resolve($props['drawer']),
			'icon'    => $resolve($props['icon']),
			'link'    => $resolve($props['link']),
			'text'    => $resolve($props['text']),
			'theme'   => $resolve($props['theme']),
			'options' => $options
		];
	}

	/**
	 * Transforms a closure to the actual view button
	 * by calling it with the provided arguments
	 */
	public static function resolve(
		Closure $button,
		ModelWithContent|Language|null $model = null,
		array $data = []
	): static|array|null {
		$kirby      = App::instance();
		$controller = new Controller($button);

		if (
			$model instanceof ModelWithContent ||
			$model instanceof Language
		) {
			$data = [
				'model'             => $model,
				$model::CLASS_ALIAS => $model,
				...$data
			];
		}

		return $controller->call(data: [
			'kirby' => $kirby,
			'site'  => $kirby->site(),
			'user'  => $kirby->user(),
			...$data
		]);
	}
}
