<?php

namespace Kirby\Template;

use Kirby\Cms\App;
use Kirby\Exception\LogicException;
use Kirby\Toolkit\Tpl;

/**
 * The Snippet class handles reusable code parts, components,
 * layouts or however we want to call it in templates
 * allows to pass variables as well as content via predefined slots.
 *
 * @package   Kirby Template
 * @author    Bastian Allgeier <bastian@getkirby.com>,
 * 			  Lukas Bestle <lukas@getkirby.com>,
 * 			  Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class Snippet
{
	/**
	 * Contains all slots that are currently
	 * opened but not yet closed
	 */
	public array $capture = [];

	/**
	 * Cache for the currently active
	 * snippet. This is used to start
	 * and end slots within this snippet
	 * by the helper functions
	 */
	public static self|null $current = null;

	/**
	 * A parent snippet
	 */
	public self|null $parent = null;

	/**
	 * Keeps track of the state of the snippet
	 * when it contains slots
	 */
	public bool $open = false;

	/**
	 * The collection of closed slots that will be used
	 * to pass down to the template for the snippet.
	 */
	public array $slots = [];

	protected bool $rendered = false;

	/**
	 * Creates a new snippet
	 */
	public function __construct(
		public string $name,
		public array $props = [],
		public string $root = ''
	) {
	}

	/**
	 * Creates and opens a new snippet. This can be used
	 * directly in a template or via the slots() helper
	 */
	public static function begin(string $name, array $props = [], string|null $root = null): static
	{
		return static::factory($name, $props, $root)->open();
	}

	/**
	 * Closes the snippet and catches
	 * the default slot if no slots have been
	 * defined in between opening and closing.
	 */
	public function close(): static
	{
		// make sure that ending a snippet
		// is only supported if the snippet has
		// been started before
		if ($this->open === false) {
			throw new LogicException('The snippet has not been opened');
		}

		// switch back to the parent in nested
		// snippet stacks
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

	public function exists(): bool
	{
		return file_exists($this->file()) === true;
	}

	/**
	 * Creates a new snippet
	 */
	public static function factory(string $name, array $props = [], string|null $root = null): static
	{
		$kirby   = App::instance();
		$snippet = new static(
			name: $name,
			props: array_replace_recursive($kirby->data, $props),
			root: $root ?? $kirby->root('snippets'),
		);

		return $snippet;
	}

	/**
	 * Absolute path to the template file for
	 * the snippet
	 */
	public function file(): string
	{
		$file = $this->root . '/' . $this->name . '.php';

		// TODO: is this the right palce? somehow clashes with the concept of a root property
		// snippet from plugins
		if (file_exists($file) === false) {
			if ($extensions = App::instance()->extensions('snippets')[$this->name] ?? null) {
				$file = $extensions;
			}
		}

		return $file;
	}

	/**
	 * Opens the snippet and starts output
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
	 * Renders the snippet and passes the scope
	 * with all slots and props
	 */
	public function render(array $props = [], array $slots = []): string
	{
		// always make sure that the snippet
		// is closed before it can be rendered
		if ($this->open === true) {
			$this->close();
		}

		// manually add slots
		foreach ($slots as $slotName => $slotContent) {
			$this->slots[$slotName] = new Slot($this, $slotName, $slotContent);
		}

		$this->rendered = true;
		$data = $this->scope($props);
		return Tpl::load($this->file(), $data);
	}

	/**
	 * Defines the full scope that will be passed
	 * to the snippet template. This includes
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

	public function __destruct()
	{
		if ($this->rendered === false) {
			$this->render();
		}
	}

	public function __toString()
	{
		$this->render();
	}
}
