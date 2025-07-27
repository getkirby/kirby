<?php

namespace Kirby\Panel\Routes;

use Closure;
use Kirby\Panel\Area;
use Kirby\Exception\InvalidArgumentException;

/**
 * @package   Kirby Panel
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
abstract class Routes
{
	protected static string $prefix = '/';
	protected static string $type = '';

	public function __construct(
		protected Area $area,
		protected array $routes,
	) {
	}

	/**
	 * Creates a controller closure from a class name
	 */
	public function controller(string|Closure|null $controller): Closure|null
	{
		if (is_string($controller) === true) {
			$expect = 'Kirby\Panel\Controller\\' . ucfirst(static::$type) . 'Controller';

			if (is_subclass_of($controller, $expect) === false) {
				throw new InvalidArgumentException(
					message: 'Invalid controller class "' . $controller . '" expected child of"' . $expect . '"'
				);
			}

			if (method_exists($controller, 'factory') === true) {
				return $controller::factory(...);
			}

			return fn (...$args) => new $controller(...$args);
		}

		return null;
	}

	public function params(Closure|array $params): array
	{
		// support direct handler
		if ($params instanceof Closure) {
			$params = [
				'action' => $params
			];
		}

		return $params;
	}

	/**
	 * Builds the full routing pattern with the
	 * given prefix
	 */
	public function pattern(
		string $pattern
	) {
		return trim(static::$prefix . '/' . $pattern, '/');
	}

	/**
	 * Creates a single route and injects
	 * type and a area id
	 */
	public function route(
		array|string $pattern,
		Closure $action,
		string $method = 'GET',
		bool $auth = true,
	) {
		return [
			'auth'    => $auth,
			'pattern' => $pattern,
			'type'    => static::$type,
			'area'    => $this->area->id(),
			'method'  => $method,
			'action'  => $action
		];
	}

	abstract public function toArray(): array;
}
