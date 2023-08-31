<?php

namespace Kirby\Cms;

use Kirby\Content\Field;
use Kirby\Toolkit\Str;

/**
 * The Item class is the foundation
 * for every object in context with
 * other objects. I.e.
 *
 * - a Block in a collection of Blocks
 * - a Layout in a collection of Layouts
 * - a Column in a collection of Columns
 * @since 3.5.0
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class Item
{
	use HasSiblings;

	public const ITEMS_CLASS = Items::class;

	protected Field|null $field;

	protected string $id;
	protected array $params;
	protected ModelWithContent $parent;
	protected Items $siblings;

	/**
	 * Creates a new item
	 */
	public function __construct(array $params = [])
	{
		$class          = static::ITEMS_CLASS;
		$this->id       = $params['id']       ?? Str::uuid();
		$this->params   = $params;
		$this->field    = $params['field']    ?? null;
		$this->parent   = $params['parent']   ?? App::instance()->site();
		$this->siblings = $params['siblings'] ?? new $class();
	}

	/**
	 * Static Item factory
	 */
	public static function factory(array $params): static
	{
		return new static($params);
	}

	/**
	 * Returns the parent field if known
	 */
	public function field(): Field|null
	{
		return $this->field;
	}

	/**
	 * Returns the unique item id (UUID v4)
	 */
	public function id(): string
	{
		return $this->id;
	}

	/**
	 * Compares the item to another one
	 */
	public function is(Item $item): bool
	{
		return $this->id() === $item->id();
	}

	/**
	 * Returns the Kirby instance
	 */
	public function kirby(): App
	{
		return $this->parent()->kirby();
	}

	/**
	 * Returns the parent model
	 */
	public function parent(): ModelWithContent
	{
		return $this->parent;
	}

	/**
	 * Returns the sibling collection
	 * This is required by the HasSiblings trait
	 *
	 * @psalm-return self::ITEMS_CLASS
	 */
	protected function siblingsCollection(): Items
	{
		return $this->siblings;
	}

	/**
	 * Converts the item to an array
	 */
	public function toArray(): array
	{
		return [
			'id' => $this->id(),
		];
	}
}
