<?php

namespace Kirby\Cms;

/**
 * The Structure class wraps
 * array data into a nicely chainable
 * collection with objects and Kirby-style
 * content with fields. The Structure class
 * is the heart and soul of our yaml conversion
 * method for pages.
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 *
 * @extends \Kirby\Cms\Items<\Kirby\Cms\StructureObject>
 */
class Structure extends Items
{
	public const ITEM_CLASS = StructureObject::class;

	/**
	 * All registered structure methods
	 */
	public static array $methods = [];

	/**
	 * Creates a new structure collection from a
	 * an array of item props
	 */
	public static function factory(
		array|null $items = null,
		array $params = []
	): static {
		if (is_array($items) === true) {
			$items = array_map(function ($item, $index) {
				if (is_array($item) === true) {
					// pass a clean content array without special `Item` keys
					$item['content'] = $item;

					// bake-in index as ID for all items
					// TODO: remove when adding UUID supports to Structures
					$item['id'] ??= $index;
				}

				return $item;
			}, $items, array_keys($items));
		}

		return parent::factory($items, $params);
	}
}
