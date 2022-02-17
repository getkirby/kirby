<?php

namespace Kirby\Form;

use Closure;
use Kirby\Toolkit\Collection;

/**
 * A collection of Field objects
 *
 * @package   Kirby Form
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
class Fields extends Collection
{
    /**
     * Internal setter for each object in the Collection.
     * This takes care of validation and of setting
     * the collection prop on each object correctly.
     *
     * @param string $name
     * @param object|array $field
     * @return $this
     */
    public function __set(string $name, $field)
    {
        if (is_array($field) === true) {
            // use the array key as name if the name is not set
            $field['name'] ??= $name;
            $field = Field::factory($field['type'], $field, $this);
        }

        return parent::__set($field->name(), $field);
    }

    /**
     * Converts the fields collection to an
     * array and also does that for every
     * included field.
     *
     * @param \Closure|null $map
     * @return array
     */
    public function toArray(Closure $map = null): array
    {
        $array = [];

        foreach ($this as $field) {
            $array[$field->name()] = $field->toArray();
        }

        return $array;
    }
}
