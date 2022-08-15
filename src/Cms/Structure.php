<?php

namespace Kirby\Cms;

use Kirby\Exception\InvalidArgumentException;

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
 */
class Structure extends Collection
{
	/**
	 * Creates a new Collection with the given objects
	 *
	 * @param array $objects Kirby\Cms\StructureObject` objects or props arrays
	 */
	public function __construct(
		array $objects = [],
		object|null $parent = null,
		protected Field|null $field = null
	) {
		$this->parent = $parent;
		$this->set($objects);
	}

	/**
	 * The internal setter for collection items.
	 * This makes sure that nothing unexpected ends
	 * up in the collection. You can pass arrays or
	 * StructureObjects
	 *
	 * @throws \Kirby\Exception\InvalidArgumentException
	 */
	public function __set(string $id, $props): void
	{
		if ($props instanceof StructureObject) {
			$object = $props;
		} else {
			if (is_array($props) === false) {
				throw new InvalidArgumentException('Invalid structure data');
			}

			$object = new StructureObject([
				'content'    => $props,
				'id'         => $props['uuid'] ?? $id,
				'field'      => $this->field,
				'parent'     => $this->parent,
				'structure'  => $this
			]);
		}

		parent::__set($object->id(), $object);
	}
}
