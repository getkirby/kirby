<?php

namespace Kirby\Object;

use Exception;

class Attributes
{

    public static function create($attributes = null, array $schema): array
    {

        $result = [];

        foreach ($schema as $attribute => $setup) {

            if (($setup['required'] ?? false) === true && (isset($attributes[$attribute]) === false || $attributes[$attribute] === null)) {
                throw new Exception(sprintf('The "%s" attribute is missing', $attribute));
            }

            $result[$attribute] = $value = $attributes[$attribute] ?? null;

            if ($value === null) {
                continue;
            }

            $error = 'The "%s" attribute must be of type "%s" not "%s"';

            if (is_object($value)) {
                if (is_a($value, $setup['type']) !== true) {
                    throw new Exception(sprintf($error, $attribute, $setup['type'], get_class($value)));
                }
            } elseif ($setup['type'] !== $type = gettype($value)) {
                throw new Exception(sprintf($error, $attribute, $setup['type'], $type));
            }

        }

        return $result;

    }

}
