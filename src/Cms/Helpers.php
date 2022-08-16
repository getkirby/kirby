<?php

namespace Kirby\Cms;

use Closure;
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
	 * Performs an action with custom handling
	 * for all PHP errors and warnings
	 * @since 3.7.4
	 *
	 * @param \Closure $action Any action that may cause an error or warning
	 * @param \Closure $handler Custom callback like for `set_error_handler()`;
	 *                          the first argument is a return value override passed
	 *                          by reference, the additional arguments come from
	 *                          `set_error_handler()`; returning `false` activates
	 *                          error handling by Whoops and/or PHP
	 * @return mixed Return value of the `$action` closure, possibly overridden by `$handler`
	 */
	public static function handleErrors(Closure $action, Closure $handler)
	{
		$override = $oldHandler = null;
		$oldHandler = set_error_handler(function () use (&$override, &$oldHandler, $handler) {
			$handlerResult = $handler($override, ...func_get_args());

			if ($handlerResult === false) {
				// handle other warnings with Whoops if loaded
				if (is_callable($oldHandler) === true) {
					return $oldHandler(...func_get_args());
				}

				// otherwise use the standard error handler
				return false; // @codeCoverageIgnore
			}

			// no additional error handling
			return true;
		});

		$result = $action();

		restore_error_handler();

		return $override ?? $result;
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
