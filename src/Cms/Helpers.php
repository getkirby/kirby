<?php

namespace Kirby\Cms;

use Kirby\Exception\InvalidArgumentException;
use Kirby\Toolkit\Str;

/**
 * The `Helpers` class hosts a few handy helper methods
 * @since 3.7.0
 *
 * @package   Kirby Cms
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class Helpers
{
    /**
     * Triggers a deprecation warning if debug mode is active
     *
     * @param string $message
     * @return bool Whether the warning was triggered
     */
    public static function deprecated(string $message): bool
    {
        if (App::instance()->option('debug') === true) {
            return trigger_error($message, E_USER_DEPRECATED) === true;
        }

        return false;
    }

    /**
     * Simple object and variable dumper
     * to help with debugging.
     *
     * @param mixed $variable
     * @param bool $echo
     * @return string
     */
    public static function dump($variable, bool $echo = true): string
    {
        $kirby = App::instance();
        return ($kirby->component('dump'))($kirby, $variable, $echo);
    }

    /**
     * Checks if a helper was overridden by the user
     * by setting the `KIRBY_HELPER_*` constant
     * @internal
     *
     * @param string $name Name of the helper
     * @return bool
     */
    public static function hasOverride(string $name): bool
    {
        $name = 'KIRBY_HELPER_' . strtoupper($name);
        return defined($name) === true && constant($name) === false;
    }

    /**
     * Determines the size/length of numbers,
     * strings, arrays and countable objects
     *
     * @param mixed $value
     * @return int
     * @throws \Kirby\Exception\InvalidArgumentException
     */
    public static function size($value): int
    {
        if (is_numeric($value)) {
            return (int)$value;
        }

        if (is_string($value)) {
            return Str::length(trim($value));
        }

        if (is_countable($value)) {
            return count($value);
        }

        throw new InvalidArgumentException('Could not determine the size of the given value');
    }
}
