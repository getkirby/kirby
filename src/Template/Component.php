<?php

namespace Kirby\Template;

use Kirby\Cms\App;
use Kirby\Exception\LogicException;
use Kirby\Toolkit\Tpl;

/**
 * The component class handles
 * components, layouts or however we want to call it
 * in templates and allows to pass content to various
 * predefined slots.
 *
 * @package   Kirby Template
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class Component
{
	/**
	 * Contains all slots that are opened
	 * but not yet closed
	 */
	public array $capture = [];

	/**
	 * Cache for the currently active
	 * component. This is used to start
	 * and end slots within this component
	 * in the helper functions
	 */
	public static self|null $current = null;

	/**
	 * The parent component
	 */
	public self|null $parent = null;

	/**
	 * Keeps track of the state of the component
	 */
	public bool $open = false;

	/**
	 * Self-closing components can be useful if all
	 * slots are optional or no slots are defined
	 * They will be rendered immediately after being
	 * openend.
	 */
	public bool $selfClosing = false;

	/**
	 * The collection of closed slots that will be used
	 * to pass down to the template for the component.
	 */
	public array $slots = [];

	/**
	 * Creates a new component
	 */
	public function __construct(
		public string $name,
		public array $props = [],
		public string $root = '',
	) {
		if (str_ends_with($this->name, '/') === true) {
			$this->name        = rtrim($this->name, '/');
			$this->selfClosing = true;
		}
	}

	/**
	 * Creates and opens a new component. This can be used
	 * directly in a template or via the slots() helper
	 */
	public static function begin(string $name, array $props = [], string|null $root = null): static
	{
		$kirby     = App::instance();
		$component = new static(
			name: $name,
			props: array_replace_recursive($kirby->data, $props),
			root: $root ?? $kirby->root('components'),
		);

		return $component->open();
	}

	/**
	 * Closes the component and catches
	 * the default slot if no slots have been
	 * defined in between opening and closing.
	 */
	public function close(): static
	{
		// make sure that ending a component
		// is only supported if the component has
		// been started before
		if ($this->open === false) {
			throw new LogicException('The component has not been opened');
		}

		// switch back to the parent in nested
		// component stacks
		static::$current = $this->parent;

		// create a default slot for the content
		// that has been captured between start and end
		if (empty($this->slots) === true) {
			$this->slots['default'] = new Slot($this, 'default');
			$this->slots['default']->content = ob_get_clean();
		} else {
			// swallow any "unslotted" content
			// between start and end
			ob_end_clean();
		}

		$this->open = false;

		return $this;
	}

	/**
	 * Used in the endslots() helper
	 */
	public static function end(): void
	{
		echo static::$current?->render();
	}

	/**
	 * Closes the last openend slot
	 */
	public function endslot(): void
	{
		// take the last slot from the capture stack
		$slot = array_pop($this->capture);

		// capture the content and close the slot
		$slot->close();

		// add the slot to the scope
		$this->slots[$slot->name] = $slot;
	}

	/**
	 * Absolute path to the template file for
	 * the component
	 */
	public function file(): string
	{
		return $this->root . '/' . $this->name . '.php';
	}

	/**
	 * Opens the component and starts output
	 * buffering to catch all slots in between
	 */
	public function open(): static
	{
		if (static::$current !== null) {
			$this->parent = static::$current;
		}

		$this->open = true;
		static::$current = $this;

		ob_start();

		return $this;
	}

	/**
	 * Renders the component and passes the scope
	 * with all slots and props
	 */
	public function render(array $props = [], array $slots = []): string
	{
		// always make sure that the component
		// is closed before it can be rendered
		if ($this->open === true) {
			$this->close();
		}

		// manually add slots
		foreach ($slots as $slotName => $slotContent) {
			$this->slots[$slotName] = new Slot($this, $slotName, $slotContent);
		}

		return Tpl::load($this->file(), $this->scope($props));
	}

	/**
	 * Defines the full scope that will be passed
	 * to the component template. This includes
	 * the props from the constructor and
	 * the slots collection.
	 */
	public function scope(array $props = []): array
	{
		$slots = $this->slots();
		$props = array_replace_recursive($this->props, $props);

		return array_merge($props, [
			'props' => $props,
			'slot'  => $slots->default,
			'slots' => $slots,
		]);
	}

	/**
	 * Starts a new slot with the given name
	 */
	public function slot(string $name = 'default'): void
	{
		$slot = new Slot($this, $name);
		$slot->open();

		// start a new slot
		$this->capture[] = $slot;
	}

	/**
	 * Returns the slots collection
	 */
	public function slots(): Slots
	{
		return new Slots($this, $this->slots);
	}
}
