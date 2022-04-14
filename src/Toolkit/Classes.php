<?php

namespace Kirby\Toolkit;

/**
 * Classes helper method
 *
 * @package   Kirby Toolkit
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier GmbH
 * @license   https://opensource.org/licenses/MIT
 * @since     3.6.2
 */
class Classes
{
    /**
     * A super simple class autoloader
     * @since 3.6.2
     *
     * @param array $classmap
     * @param string|null $base
     * @return void
     */
    public static function load(array $classmap, ?string $base = null)
    {
        // convert all classnames to lowercase
        $classmap = array_change_key_case($classmap);

        spl_autoload_register(function ($class) use ($classmap, $base) {
            $class = strtolower($class);

            if (!isset($classmap[$class])) {
                return false;
            }

            if ($base) {
                include $base . '/' . $classmap[$class];
            } else {
                include $classmap[$class];
            }
        });
    }
}
