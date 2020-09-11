<?php

namespace Kirby\Cms;

use Kirby\Data\Data;
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
 * @copyright Bastian Allgeier GmbH
 * @license   https://getkirby.com/license
 */
class Structure extends Collection
{
    /**
     * Creates a new Collection with the given objects
     *
     * @param array $objects
     * @param object|null $parent
     */
    public function __construct($objects = [], $parent = null)
    {
        $this->parent = $parent;
        $this->set($objects);
    }

    /**
     * The internal setter for collection items.
     * This makes sure that nothing unexpected ends
     * up in the collection. You can pass arrays or
     * StructureObjects
     *
     * @param string $id
     * @param array|StructureObject $props
     * @throws \Kirby\Exception\InvalidArgumentException
     */
    public function __set(string $id, $props)
    {
        if (is_a($props, 'Kirby\Cms\StructureObject') === true) {
            $object = $props;
        } else {
            if (is_array($props) === false) {
                throw new InvalidArgumentException('Invalid structure data');
            }

            $object = new StructureObject([
                'content'    => $props,
                'id'         => $props['id'] ?? $id,
                'parent'     => $this->parent,
                'structure'  => $this
            ]);
        }

        return parent::__set($object->id(), $object);
    }

    /**
     * Converts structure value to array
     * and merges default language data for all languages
     * for non-translatable fields
     *
     * @param string $fieldName
     * @param mixed $value
     * @param object|null $parent
     * @return array
     */
    public static function toData(string $fieldName, $value, $parent = null): array
    {
        $data  = Data::decode($value, 'yaml');
        $kirby = is_a($parent, '\Kirby\Cms\Model') === true ? $parent->kirby() : App::instance();

        // merge with the default content
        if ($parent !== null && $kirby->multilang() === true) {
            $language        = $kirby->language();
            $defaultLanguage = $kirby->defaultLanguage();

            if ($language->code() !== $defaultLanguage->code()) {
                if ($content = $parent->content($defaultLanguage->code())->get($fieldName)) {
                    $default = Data::decode($content->value(), 'yaml');
                    $data    = array_replace_recursive($default, $data);
                }
            }
        }

        return $data;
    }
}
