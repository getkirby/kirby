<?php

namespace Kirby\Cms;

use Closure;
use Exception;
use Kirby\Util\Str;

class Extend
{

    protected static function callbacks(string $type, array $callbacks): array
    {
        foreach ($callbacks as $name => $callback) {
            if (is_string($name) === false) {
                throw new Exception(sprintf('Invalid "%s" name', $type));
            }

            if (is_a($callback, Closure::class) === false) {
                throw new Exception(sprintf('Invalid "%s" definition for "%s"', $type, $name));
            }
        }

        return $callbacks;
    }

    public static function collections(array $collections): array
    {
        return static::callbacks('collection', $collections);
    }

    public static function controllers(array $controllers): array
    {
        return static::callbacks('controller', $controllers);
    }

    public static function blueprints(array $blueprints): array
    {
        return $blueprints;
    }

    public static function fields(array $fields, Plugin $plugin = null): array
    {
        $result = [];

        foreach ($fields as $name => $class) {

            if (is_string($name) === false) {
                throw new Exception('Invalid field type');
            }

            if (is_string($class) === false) {
                throw new Exception('Invalid field class definition');
            }

            $result[$name] = [
                'class'  => $class,
                'plugin' => $plugin
            ];
        }

        return $result;
    }

    public static function fieldMethods(array $fieldMethods): array
    {
        return static::callbacks('fieldMethod', $fieldMethods);
    }

    /**
     * Makes sure to put all hooks in a nice array
     *
     * [
     *   'page.delete:before' => [
     *      function () {},
     *      function () {}
     *   ]
     * ]
     *
     * @param array $hooks
     * @return array
     */
    public static function hooks(array $hooks): array
    {
        $result = [];

        foreach ($hooks as $name => $callbacks) {

            if (is_string($name) === false) {
                throw new Exception('Invalid hook name');
            }

            if (is_array($callbacks) === false) {
                $callbacks = [$callbacks];
            }

            $result[$name] = [];

            foreach ($callbacks as $callback) {
                if (is_a($callback, Closure::class) === false) {
                    throw new Exception('Invalid hook function');
                }

                $result[$name][] = $callback;
            }

        }

        return $result;
    }

    protected static function mixed(string $type, array $mixed): array
    {
        foreach ($mixed as $name => $value) {
            if (is_string($name) === false) {
                throw new Exception(sprintf('Invalid "%s" name', $type));
            }
        }

        return $mixed;
    }

    public static function pages(array $pages): array
    {
        return $pages;
    }

    public static function pageModels(array $pageModels): array
    {
        return static::strings('pageModel', $pageModels);
    }

    public static function options(array $options, Plugin $plugin = null): array
    {
        $options = static::mixed('option', $options);

        if ($plugin === null) {
            return $options;
        }

        $prefixed = [];

        foreach ($options as $key => $value) {
            $prefixed[$plugin->prefix() . '.' . $key] = $value;
        }

        return $prefixed;
    }

    public static function routes(array $routes): array
    {
        $result = [];

        foreach ($routes as $name => $route) {
            if (is_array($route) === false) {
                throw new Exception('Each route must be defined as array');
            }

            if (isset($route['pattern'], $route['action']) === false) {
                throw new Exception('Each route must define at least a pattern and an action');
            }

            $result[$name] = $route;
        }

        return $result;
    }

    public static function snippets(array $snippets): array
    {
        return static::strings('snippet', $snippets);
    }

    protected static function strings(string $type, array $strings): array
    {
        foreach ($strings as $name => $string) {
            if (is_string($name) === false) {
                throw new Exception(sprintf('Invalid "%s" name', $type));
            }

            if (is_string($string) === false) {
                throw new Exception(sprintf('Invalid "%s" definition for "%s"', $type, $name));
            }
        }

        return $strings;
    }

    public static function tags(array $tags): array
    {
        return static::strings('tag', $tags);
    }

    public static function templates(array $templates): array
    {
        return static::strings('template', $templates);
    }

}
