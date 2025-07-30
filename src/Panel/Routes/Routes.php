<?php

namespace Kirby\Panel\Routes;

use Closure;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Panel\Area;

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
	public function controller(array $params): array
	{
		if ($controller = $params['action'] ?? null) {
			if (is_string($controller) === true) {
				// Ensure controller uses the correct interface
				$expect = 'Kirby\Panel\Controller\\' . ucfirst(static::$type) . 'Controller';

				if (is_subclass_of($controller, $expect) === false) {
					throw new InvalidArgumentException(
						message: 'Invalid controller class "' . $controller . '" expected child of"' . $expect . '"'
					);
				}

				// Use factory method if available
				if (method_exists($controller, 'factory') === true) {
					$controller = $controller::factory(...);
				} else {
					$controller = fn (...$args) => new $controller(...$args);
				}

				// Add controller closures to params
				$params['load']   ??= fn (...$args) => $controller(...$args)->load();
				$params['submit'] ??= fn (...$args) => $controller(...$args)->submit();
			}
		}

		return $params;
	}

	public function params(
		Closure|array $params,
		string|null $action = null
	): array {
		// support direct handler
		if ($params instanceof Closure) {
			$params = [
				'action' => $params
			];
		}

		// support old action handler
		if ($action && isset($params[$action]) === true) {
			$params['action'] ??= $params[$action];
		}

		// add controller from class name
		$params = $this->controller($params);

		// map action to load handler
		$params['load'] ??= $params['action'] ?? null;

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
