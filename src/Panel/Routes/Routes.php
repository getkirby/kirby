<?php

namespace Kirby\Panel\Routes;

use Closure;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Panel\Area;
use Kirby\Panel\Controller\Controller;

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
	 * Resolves a route's action controller
	 */
	public function controller(array $params): array
	{
		if ($controller = $params['action'] ?? null) {
			if (is_string($controller) === true) {
				$controller = $this->controllerFromClass($controller);
			}

			$params['load']   ??= $this->controllerHandler($controller, 'load');
			$params['submit'] ??= $this->controllerHandler($controller, 'submit', false);
		}

		return $params;
	}

	/**
	 * Creates a route handler closure which handles
	 * array results as well as resolving a controller object
	 */
	protected function controllerHandler(
		Closure|Controller|array $controller,
		string $method,
		bool $preserve = true
	): Closure {
		return function (...$args) use ($controller, $method, $preserve) {
			if ($controller instanceof Closure) {
				$controller = $controller(...$args);
			}

			if ($controller instanceof Controller) {
				return $controller->$method();
			}

			if ($preserve === true) {
				return $controller;
			}

			return true;
		};
	}

	/**
	 * Creates a controller closure from a Controller class name
	 */
	protected function controllerFromClass(string $class): Closure
	{
		// Ensure controller uses the correct interface
		$expect = 'Kirby\Panel\Controller\\' . ucfirst(static::$type) . 'Controller';

		if (is_subclass_of($class, $expect) === false) {
			throw new InvalidArgumentException(
				message: 'Invalid controller class "' . $class . '" expected child of"' . $expect . '"'
			);
		}

		// Use factory method if available
		if (method_exists($class, 'factory') === true) {
			return $class::factory(...);
		}

		return fn (...$args) => new $class(...$args);
	}

	public function params(
		Closure|string|array $params,
		string|null $action = null
	): array {
		// support direct handler
		if ($params instanceof Closure || is_string($params) === true) {
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
