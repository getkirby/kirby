<?php

namespace Kirby\Form;

use Closure;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Toolkit\Collection;

/**
 * A collection of Field objects
 */
class Fields extends Collection
{

    /**
     * Internal setter for each object in the Collection.
     * This takes care of validation and of setting
     * the collection prop on each object correctly.
     *
     * @param string $id
     * @param object $object
     */
    public function __set(string $name, $field)
    {
        if (is_array($field)) {
            // use the array key as name if the name is not set
            $field['name'] = $field['name'] ?? $name;
            $field = new Field($field);
        }

        if (is_a($field, 'Kirby\Form\Field') === false) {
            throw new InvalidArgumentException('Invalid Field object in Fields collection');
        }

        return parent::__set($field->name(), $field);
    }

    public function toArray(Closure $map = null): array
    {
        $array = [];

        foreach ($this as $field) {
            $array[$field->name()] = $field->toArray();
        }

        return $array;
    }

    public function toOptions(): array
    {
        $array = [];

        foreach ($this as $field) {
            $options = $field->toArray();
            unset($options['value']);

            $array[$field->name()] = $options;
        }

        return $array;
    }
}
