<?php

namespace Kirby\Toolkit;

use Closure;
use Kirby\Filesystem\F;
use ReflectionFunction;

/**
 * A smart extension of Closures with
 * magic dependency injection based on the
 * defined variable names.
 *
 * @package   Kirby Toolkit
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
class Controller
{
	protected $function;

	public function __construct(Closure $function)
	{
		$this->function = $function;
	}

	public function arguments(array $data = []): array
	{
		$info = new ReflectionFunction($this->function);
		$args = [];

		foreach ($info->getParameters() as $parameter) {
			$name = $parameter->getName();
			$args[] = $data[$name] ?? null;
		}

		return $args;
	}

	public function call($bind = null, $data = [])
	{
		$args = $this->arguments($data);

		if ($bind === null) {
			return call_user_func($this->function, ...$args);
		}

		return $this->function->call($bind, ...$args);
	}

	public static function load(string $file)
	{
		if (is_file($file) === false) {
			return null;
		}

		$function = F::load($file);

		if ($function instanceof Closure === false) {
			return null;
		}

		return new static($function);
	}
}
