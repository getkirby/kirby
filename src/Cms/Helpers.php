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
	 * Allows to disable specific deprecation warnings
	 * by setting them to `false`.
	 * You can do this by putting the following code in
	 * `site/config/config.php`:
	 *
	 * ```php
	 * Helpers::$deprecations['<deprecation-key>'] = false;
	 * ```
	 */
	public static $deprecations = [
		// Passing the $slot or $slots variables to snippets is
		// deprecated and will break in a future version.
		'snippet-pass-slots'    => true,

		// The `Toolkit\Query` class has been deprecated and will
		// be removed in a future version. Use `Query\Query` instead:
		// Kirby\Query\Query::factory($query)->resolve($data).
		'toolkit-query-class'   => true,

		// Passing an empty string as value to `Xml::attr()` has been
		// deprecated. In a future version, passing an empty string won't
		// omit the attribute anymore but render it with an empty value.
		// To omit the attribute, please pass `null`.
		'xml-attr-empty-string' => false,
	];

	/**
	 * Triggers a deprecation warning if debug mode is active
	 * and warning has not been surpressed via `Helpers::$deprecations`
	 *
	 * @param string|null $key If given, the key will be checked against the static array
	 * @return bool Whether the warning was triggered
	 */
	public static function deprecated(string $message, string|null $key = null): bool
	{
		// only trigger warning in debug mode or when running PHPUnit tests
		// @codeCoverageIgnoreStart
		if (
			App::instance()->option('debug') !== true &&
			(defined('KIRBY_TESTING') !== true || KIRBY_TESTING !== true)
		) {
			return false;
		}
		// @codeCoverageIgnoreEnd

		// don't trigger the warning if disabled by default or by the dev
		if ($key !== null && (static::$deprecations[$key] ?? true) === false) {
			return false;
		}

		return trigger_error($message, E_USER_DEPRECATED) === true;
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

		if ($kirby->environment()->cli() === true) {
			$output = print_r($variable, true) . PHP_EOL;
		} else {
			$output = '<pre>' . print_r($variable, true) . '</pre>';
		}

		if ($echo === true) {
			echo $output;
		}

		return $output;
	}

	/**
	 * Performs an action with custom handling
	 * for all PHP errors and warnings
	 * @since 3.7.4
	 *
	 * @param \Closure $action Any action that may cause an error or warning
	 * @param \Closure $condition Closure that returns bool to determine if to
	 *                            suppress an error, receives arguments for
	 *                            `set_error_handler()`
	 * @param mixed $fallback Value to return when error is suppressed
	 * @return mixed Return value of the `$action` closure,
	 *               possibly overridden by `$fallback`
	 */
	public static function handleErrors(Closure $action, Closure $condition, $fallback = null)
	{
		$override = null;

		$handler = set_error_handler(function () use (&$override, &$handler, $condition, $fallback) {
			// check if suppress condition is met
			$suppress = $condition(...func_get_args());

			if ($suppress !== true) {
				// handle other warnings with Whoops if loaded
				if (is_callable($handler) === true) {
					return $handler(...func_get_args());
				}

				// otherwise use the standard error handler
				return false; // @codeCoverageIgnore
			}

			// use fallback to override return for suppressed errors
			$override = $fallback;

			if (is_callable($override) === true) {
				$override = $override();
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
