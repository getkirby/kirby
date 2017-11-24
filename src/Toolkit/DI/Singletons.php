<?php

namespace Kirby\Toolkit\DI;

/**
 * If your class only depends on
 * singletons, you can use this extension
 * of the Dependencies class to register them.
 *
 * @package   Kirby Toolkit
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   MIT
 */
class Singletons extends Dependencies
{

    /**
     * Registers a new singleton
     *
     * @param  string                $name
     * @param  string|Closure|object $object
     * @param  array                 $options
     * @return Dependencies
     */
    public function set(string $name, $object, array $options = []): parent
    {
        return parent::set($name, $object, array_merge($options, ['singleton' => true]));
    }
}
