<?php

namespace Kirby\Toolkit;

use Exception;

/**
 * Macroable
 *
 * Allows classes of this trait to have programatically added static methods
 *
 * @package   Kirby Toolkit
 * @author    Adam Kiss <iam@adamkiss.com>
 * @link      https://getkirby.com
 * @copyright Adam Kiss
 * @license   https://opensource.org/licenses/MIT
 */
trait Macroable {
	private static $_macros = [];

	/**
	 * Adds a macro to the class
	 *
	 * @param string $name
	 * @param callable $macro
	 * @return void
	 */
	public static function _addMacro(string $name, callable $macro) {
		if (method_exists(static::class, $name)) {
			throw new Exception('Class "' . static::class . "\" already contains static method \"{$name}\"");
		}

		if (array_key_exists($name, static::$_macros)) {
			throw new Exception('Class "' . static::class . "\" already includes macro \"{$name}\"");
		}

		static::$_macros[$name] = $macro;
	}

	/**
	 * Checks if a macro exists
	 *
	 * @param string $name
	 * @return bool
	 */
	public static function _hasMacro(string $name): bool {
		return array_key_exists($name, static::$_macros);
	}

	/**
	 * Calls a macro if it exists (and method static::$name does not exist)
	 *
	 * @param string $name
	 * @param array $arguments
	 * @return mixed
	 */
	public static function __callStatic($name, $arguments) {
		if (! static::_hasMacro($name)) {
			throw new Exception('Class "' . self::class . "\" does not contain static method \"{$name}\"");
		}

		$macro = static::$_macros[$name];

		if ($macro instanceof \Closure) {
			return \Closure::bind($macro, null, static::class)(...$arguments);
		}

		return call_user_func_array($macro, $arguments);
	}
}
