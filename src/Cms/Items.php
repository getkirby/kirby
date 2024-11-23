<?php

namespace Kirby\Cms;

use Closure;
use Kirby\Content\Field;
use Kirby\Exception\InvalidArgumentException;

/**
 * A collection of items
 * @since 3.5.0
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class Items extends Collection
{
	public const ITEM_CLASS = Item::class;

	protected Field|null $field;

	/**
	 * All registered items methods
	 */
	public static array $methods = [];

	protected array $options;

	/**
	 * @var \Kirby\Cms\ModelWithContent
	 */
	protected $parent;

	public function __construct($objects = [], array $options = [])
	{
		$this->options = $options;
		$this->parent  = $options['parent'] ?? App::instance()->site();
		$this->field   = $options['field']  ?? null;

		parent::__construct($objects, $this->parent);
	}

	/**
	 * Creates a new item collection from a
	 * an array of item props
	 */
	public static function factory(
		array|null $items = null,
		array $params = []
	): static {
		if (empty($items) === true || is_array($items) === false) {
			return new static();
		}

		if (is_array($params) === false) {
			throw new InvalidArgumentException('Invalid item options');
		}

		// create a new collection of blocks
		$collection = new static([], $params);

		foreach ($items as $item) {
			if (is_array($item) === false) {
				throw new InvalidArgumentException('Invalid data for ' . static::ITEM_CLASS);
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
