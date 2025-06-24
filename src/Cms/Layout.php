<?php

namespace Kirby\Cms;

use Kirby\Content\Content;

/**
 * Represents a single Layout with
 * multiple columns
 * @since 3.5.0
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 *
 * @extends \Kirby\Cms\Item<\Kirby\Cms\Layouts>
 */
class Layout extends Item
{
	use HasMethods;

	public const ITEMS_CLASS = Layouts::class;

	protected Content $attrs;
	protected LayoutColumns $columns;

	/**
	 * Proxy for attrs
	 */
	public function __call(string $method, array $args = []): mixed
	{
		// layout methods
		if ($this->hasMethod($method) === true) {
			return $this->callMethod($method, $args);
		}

		return $this->attrs()->get($method);
	}

	/**
	 * Creates a new Layout object
	 */
	public function __construct(array $params = [])
	{
		parent::__construct($params);

		$this->columns = LayoutColumns::factory($params['columns'] ?? [], [
			'field'  => $this->field,
			'parent' => $this->parent
		]);

		// create the attrs object
		$this->attrs = new Content($params['attrs'] ?? [], $this->parent);
	}

	/**
	 * Returns the attrs object
	 */
	public function attrs(): Content
	{
		return $this->attrs;
	}

	/**
	 * Returns the columns in this layout
	 */
	public function columns(): LayoutColumns
	{
		return $this->columns;
	}

	/**
	 * Checks if the layout is empty
	 * @since 3.5.2
	 */
	public function isEmpty(): bool
	{
		return $this
			->columns()
			->filter('isEmpty', false)
			->count() === 0;
	}

	/**
	 * Checks if the layout is not empty
	 * @since 3.5.2
	 */
	public function isNotEmpty(): bool
	{
		return $this->isEmpty() === false;
	}

	/**
	 * The result is being sent to the editor
	 * via the API in the panel
	 */
	public function toArray(): array
	{
		return [
			'attrs'   => $this->attrs()->toArray(),
			'columns' => $this->columns()->toArray(),
			'id'      => $this->id(),
		];
	}
}
