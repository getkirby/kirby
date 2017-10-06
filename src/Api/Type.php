<?php

namespace Kirby\Api;

use Exception;

use GraphQL\Type\Definition\Type as BaseType;
use GraphQL\Type\Definition\ObjectType;

class Type extends BaseType
{

    protected static $types = [];
    protected static $cache = [];

    public static function __callStatic($type, $arguments = [])
    {
        return static::$cache[$type] ?? static::$cache[$type] = static::load($type);
    }

    public static function set(array $types)
    {
        static::$types = $types;
    }

    protected static function load(string $type)
    {
        if (isset(static::$types[$type]) === true) {
            $definition = static::$types[$type]();

            if (is_array($definition)) {
                return new ObjectType($definition);
            } elseif (is_object($definition)) {
                return $definition;
            } else {
                throw new Exception('Invalid type definition for: ' . $type);
            }

        }

        throw new Exception('The type "' . $type . '" has not been registered yet');
    }

}
