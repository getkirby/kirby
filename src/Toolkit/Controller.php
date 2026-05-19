<?php

namespace Kirby\Toolkit;

use Closure;
use Exception;
use Kirby\Filesystem\F;
use ReflectionFunction;

/**
 * A smart extension of Closures with
 * magic dependency injection based on the
 * defined variable names.
 *
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
class Controller
{
	/**
	 * Cached parameter list of the wrapped closure;
	 * @var \ReflectionParameter[]|null
	 */
	protected array|null $params = null;

	public function __construct(
		protected Closure $function
	) {
	}

	public function arguments(array $data = []): array
	{
		$this->params ??= (new ReflectionFunction($this->function))->getParameters();

		$args = [];

		foreach ($this->params as $param) {
			$name = $param->getName();

			if ($param->isVariadic() === true) {
				// variadic ... argument collects all remaining values
				$args += $data;
			} elseif (isset($data[$name]) === true) {
				// use provided argument value if available
				$args[$name] = $data[$name];
			} elseif ($param->isDefaultValueAvailable() === false) {
				// use null for any other arguments that don't define
				// a default value for themselves
				$args[$name] = null;
			}
		}

		return $args;
	}

	public function call($bind = null, $data = [])
	{
		// unwrap lazy values in arguments
		$args = $this->arguments($data);
		$args = LazyValue::unwrap($args);

		if ($bind === null) {
			return ($this->function)(...$args);
		}

		return $this->function->call($bind, ...$args);
	}

	public static function load(string $file, string|null $in = null): static|null
	{
		if (is_file($file) === false) {
			return null;
		}

		// restrict file paths to the provided root
		// to prevent path traversal
		if ($in !== null) {
			try {
				$file = F::realpath($file, $in);
			} catch (Exception) {
				// don't expose whether the file exists
				// (which would have returned `null` above)
				return null;
			}
		}

		$function = F::load($file);

		if ($function instanceof Closure === false) {
			return null;
		}

		return new static($function);
	}
}
