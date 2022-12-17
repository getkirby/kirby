<?php

namespace Kirby\Template;

use Kirby\Cms\App;
use Kirby\Exception\LogicException;
use Kirby\Toolkit\A;
use Kirby\Toolkit\Tpl;

/**
 * The Snippet class includes shared code parts
 * in templates and allows to pass data as well as to
 * optionally pass content to various predefined slots.
 *
 * @package   Kirby Template
 * @author    Bastian Allgeier <bastian@getkirby.com>,
 * 			  Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class Snippet extends Tpl
{
	/**
	 * Cache for the currently active
	 * snippet. This is used to start
	 * and end slots within this snippet
	 * in the helper functions
	 * @internal
	 */
	public static self|null $current = null;

	/**
	 * Contains all slots that are opened
	 * but not yet closed
	 */
	protected array $capture = [];

	/**
	 * The parent snippet
	 */
	protected self|null $parent = null;

	/**
	 * Keeps track of the state of the snippet
	 */
	protected bool $open = false;

	/**
	 * The collection of closed slots that will be used
	 * to pass down to the template for the snippet.
	 */
	protected array $slots = [];

	/**
	 * Creates a new snippet
	 */
	public function __construct(
		protected string $file,
		protected array $data = []
	) {
	}

	/**
	 * Creates and opens a new snippet. This can be used
	 * directly in a template or via the slots() helper
	 */
	public static function begin(string $file, array $data = []): static
	{
		$snippet = new static($file, $data);
		return $snippet->open();
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

		// switch back to the parent in nested
		// snippet stacks
		static::$current = $this->parent;

		return $this;
	}

	/**
	 * Used in the endsnippet() helper
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
		$this->slots[$slot->name()] = $slot;
	}

	/**
	 * Returns either an open snippet capturing slots
	 * or the template string for self-enclosed snippets
	 */
	public static function factory(
		string|array $name,
		array $data = [],
		bool $slots = false
	): static|string {
		$file = static::file($name);

		// for snippets with slots, make sure to open a new
		// snippet and start capturing slots
		if ($slots === true) {
			return static::begin($file, $data);
		}

		// for snippets without slots, directly load and return
		// the snippet's template file
		$data = array_merge(App::instance()->data, $data);
		return static::load($file, $data);
	}

	/**
	 * Absolute path to the file for
	 * the snippet/s taking snippets defined in plugins
	 * into account
	 */
	public static function file(string|array $name): string|null
	{
		$kirby = App::instance();
		$root  = static::root();
		$names = A::wrap($name);

		foreach ($names as $name) {
			$name = (string)$name;
			$file = $root . '/' . $name . '.php';

			if (file_exists($file) === false) {
				$file = $kirby->extensions('snippets')[$name] ?? null;
			}

			if ($file) {
				break;
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
	 * with all slots and data
	 */
	public function render(array $data = [], array $slots = []): string
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

		return static::load($this->file, $this->scope($data));
	}

	/**
	 * Returns the root directory for all
	 * snippet templates
	 */
	public static function root(): string
	{
		return App::instance()->root('snippets');
	}

	/**
	 * Defines the full scope that will be passed
	 * to the snippet template. This includes
	 * the data from the constructor and
	 * the slots collection.
	 */
	public function scope(array $data = []): array
	{
		$kirby = App::instance();
		$slots = $this->slots();
		$data  = array_replace_recursive($this->data, $data);

		return array_merge($kirby->data, $data, [
			'data'  => $data,
			'slot'  => $slots->default,
			'slots' => $slots,
		]);
	}

	/**
	 * Starts a new slot with the given name
	 */
	public function slot(string $name = 'default'): Slot
	{
		$slot = new Slot($this, $name);
		$slot->open();

		// start a new slot
		$this->capture[] = $slot;

		return $slot;
	}

	/**
	 * Returns the slots collection
	 */
	public function slots(): Slots
	{
		return new Slots($this, $this->slots);
	}
}
