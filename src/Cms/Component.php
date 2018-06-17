<?php

namespace Kirby\Cms;

use Kirby\Exception\InvalidArgumentException;
use Kirby\Toolkit\Properties;
use ReflectionMethod;

/**
 * Foundation for all other models and objects,
 * that handles toArray and toJson methods.
 *
 * TODO: refactor this. We don't necessarily need this
 */
abstract class Component
{
    use Properties;

    /**
     * Properties that should be converted to array
     *
     * @var array
     */
    protected static $toArray = [];

    /**
     * Converts the object to json
     * by using the toArray method first.
     *
     * @param boolean $pretty Enable/disable pretty printing the json output
     * @return string
     */
    public function toJson(bool $pretty = false): string
    {
        return json_encode($this->toArray(), $pretty ? JSON_PRETTY_PRINT : null);
    }

    /**
     * Creates an array of all values of public
     * getters that return a non-object value
     *
     * @return array
     */
    public function toArray(): array
    {
        $array = [];

        foreach (static::$toArray as $propertyName) {
            $getterName    = $propertyName;
            $converterName = 'convert' . $propertyName . 'toArray';

            try {

                // add the getter result to the array
                if (method_exists($this, $converterName)) {
                    $value = $this->$converterName();
                } else {
                    $method = new ReflectionMethod($this, $getterName);

                    if ($method->isStatic() === true || $method->getNumberOfRequiredParameters() > 0) {
                        throw new InvalidArgumentException('Invalid component getter: ' . get_class($this) . '::' . $getterName);
                    }

                    $value = $this->$getterName();
                }

                // don't add object if it doesn't have its own toArray method
                if (is_object($value) === false) {
                    $array[$propertyName] = $value;
                }
            } catch (Exception $e) {
                $array[$propertyName] = [
                    'error' => sprintf('%s in file %s on line %s', $e->getMessage(), $e->getFile(), $e->getLine())
                ];
            }
        }

        ksort($array);
        return $array;
    }
}
