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
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 */
class Structure extends Collection
{

    /**
     * Only accept StructureObjects for
     * items in the collection. Arrays work too.
     *
     * @var string
     */
    protected static $accept = StructureObject::class;

    /**
     * The internal setter for collection items.
     * This makes sure that nothing unexpected ends
     * up in the collection. You can pass arrays or
     * StructureObjects
     *
     * @param string $id
     * @param array|StructureObject $object
     */
    public function __set(string $id, $object)
    {
        if (is_array($object)) {
            $object = new StructureObject([
                'parent'  => $this->parent,
                'content' => $object,
                'id'      => $object['id'] ?? $id,
            ]);
        }

        if (is_a($object, static::$accept) === false) {
            throw new InvalidArgumentException(sprintf('Invalid "%s" object in collection', static::$accept));
        }

        // inject the collection for proper navigation
        $object->setCollection($this);

        return parent::__set($object->id(), $object);
    }
}
