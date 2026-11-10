<?php

namespace Kirby\Cms;

use Closure;
use Kirby\Content\Field;
use Kirby\Exception\InvalidArgumentException;

/**
 * A collection of items
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     3.5.0
 *
 * @template TValue of Item
 * @extends Collection<TValue>
 */
class Items extends Collection
{
	public const string ITEM_CLASS = Item::class;

	protected Field|null $field;

	/**
	 * All registered items methods
	 */
	public static array $methods = [];

	/**
	 * @var array<string, mixed>
	 */
	protected array $options;

	/**
	 * @var ModelWithContent
	 */
	protected object|null $parent = null;

	/**
	 * Creates a new collection of items
	 *
	 * @param iterable<TValue> $objects
	 * @param array<string, mixed> $options Options incl. the `parent` model and `field`
	 */
	public function __construct($objects = [], array $options = [])
	{
		$this->options = $options;
		$this->parent  = $options['parent'] ?? App::instance()->site();
		$this->field   = $options['field']  ?? null;

		parent::__construct($objects, $this->parent);
	}

	/**
	 * Creates a new item collection from
	 * an array of item props
	 *
	 * @param array<array<string, mixed>>|null $items
	 * @param array<string, mixed> $params Collection options, see `::__construct()`
	 */
	public static function factory(
		array|null $items = null,
		array $params = []
	): static {
		if ($items === null || $items === []) {
			return new static(options: $params);
		}

		$collection = new static([], $params);

		foreach ($items as $item) {
			if (is_array($item) === false) {
				throw new InvalidArgumentException(
					message: 'Invalid data for ' . static::ITEM_CLASS
				);
			}

			// inject properties from the parent
			$item['field']    = $collection->field();
			$item['options']  = $params['options'] ?? [];
			$item['parent']   = $collection->parent();
			$item['siblings'] = $collection;
			$item['params']   = $item;

			$class = static::ITEM_CLASS;
			$item  = $class::factory($item);
			$collection->append($item->id(), $item);
		}

		return $collection;
	}

	/**
	 * Returns the parent field if known
	 */
	public function field(): Field|null
	{
		return $this->field;
	}

	/**
	 * Convert the items to an array
	 */
	public function toArray(Closure|null $map = null): array
	{
		return array_values(parent::toArray($map));
	}
}
