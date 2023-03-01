<?php

namespace Kirby\Toolkit;

use Closure;
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
trait HasMethods
{
	public static $methods = [];

	/**
	 * Calls a macro if it exists (and method static::$name does not exist)
	 *
	 * @param string $name
	 * @param array $arguments
	 * @return mixed
	 */
	public static function __callStatic($name, $arguments)
	{
		if (! isset(static::$methods[$name])) {
			throw new Exception('Class "' . self::class . "\" does not contain method \"{$name}\"");
		}

		$method = static::$methods[$name];

		if ($method instanceof Closure) {
			return Closure::bind($method, null, static::class)(...$arguments);
		}

		return call_user_func_array($method, $arguments);
	}
}
