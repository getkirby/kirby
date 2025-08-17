<?php

namespace Kirby\Http;

use Closure;
use Exception;
use InvalidArgumentException;
use Kirby\Toolkit\A;

/**
 * @package   Kirby Http
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
class Router
{
	/**
	 * Hook that is called after each route
	 */
	protected Closure|null $afterEach;

	/**
	 * Hook that is called before each route
	 */
	protected Closure|null $beforeEach;

	/**
	 * Store for the current route,
	 * if one can be found
	 */
	protected Route|null $route = null;

	/**
	 * All registered routes, sorted by
	 * their request method. This makes
	 * it faster to find the right route
	 * later.
	 */
	protected array $routes = [
		'GET'     => [],
		'HEAD'    => [],
		'POST'    => [],
		'PUT'     => [],
		'DELETE'  => [],
		'CONNECT' => [],
		'OPTIONS' => [],
		'TRACE'   => [],
		'PATCH'   => [],
	];

	/**
	 * Creates a new router object and
	 * registers all the given routes
	 *
	 * @param array<string, \Closure> $hooks Optional `beforeEach` and `afterEach` hooks
	 */
	public function __construct(array $routes = [], array $hooks = [])
	{
		$this->beforeEach = $hooks['beforeEach'] ?? null;
		$this->afterEach  = $hooks['afterEach']  ?? null;

		foreach ($routes as $props) {
			if (isset($props['pattern'], $props['action']) === false) {
				throw new InvalidArgumentException(
					message: 'Invalid route parameters'
				);
			}

			$patterns = A::wrap($props['pattern']);
			$methods  = A::map(
				explode('|', strtoupper($props['method'] ?? 'GET')),
				'trim'
			);

			if ($methods === ['ALL']) {
				$methods = array_keys($this->routes);
			}

			foreach ($methods as $method) {
				foreach ($patterns as $pattern) {
					$this->routes[$method][] = new Route(
						$pattern,
						$method,
						$props['action'],
						$props
					);
				}
			}
		}
	}

	/**
	 * Calls the Router by path and method.
	 * This will try to find a Route object
	 * and then call the Route action with
	 * the appropriate arguments and a Result
	 * object.
	 */
	public function call(
		string|null $path = null,
		string $method = 'GET',
		Closure|null $callback = null
	) {
		$path ??= '';
		$ignore = [];
		$result = null;
		$loop   = true;

		while ($loop === true) {
			$route = $this->find($path, $method, $ignore);

			if ($this->beforeEach instanceof Closure) {
				($this->beforeEach)($route, $path, $method);
			}

			try {
				if ($callback) {
					$result = $callback($route);
				} else {
					$result = $route->action()->call(
						$route,
						...$route->arguments()
					);
				}

				$loop = false;
			} catch (Exceptions\NextRouteException) {
				$ignore[] = $route;
			}

			if ($this->afterEach instanceof Closure) {
				$final  = $loop === false;
				$result = ($this->afterEach)($route, $path, $method, $result, $final);
			}
		}

		return $result;
	}

	/**
	 * Creates a micro-router and executes
	 * the routing action immediately
	 * @since 3.7.0
	 */
	public static function execute(
		string|null $path = null,
		string $method = 'GET',
		array $routes = [],
		Closure|null $callback = null
	) {
		return (new static($routes))->call($path, $method, $callback);
	}

	/**
	 * Finds a Route object by path and method
	 * The Route's arguments method is used to
	 * find matches and return all the found
	 * arguments in the path.
	 *
	 * @param array|null $ignore (Passing null has been deprecated)
	 * @todo Remove support for `$ignore = null` in v6
	 */
	public function find(
		string $path,
		string $method,
		array|null $ignore = null
	): Route {
		if (isset($this->routes[$method]) === false) {
			throw new InvalidArgumentException(
				message: 'Invalid routing method: ' . $method,
				code: 400
			);
		}

		// remove leading and trailing slashes
		$path     = trim($path, '/');
		$ignore ??= [];

		foreach ($this->routes[$method] as $route) {
			$arguments = $route->parse($route->pattern(), $path);

			if ($arguments !== false) {
				if (in_array($route, $ignore, true) === false) {
					return $this->route = $route;
				}
			}
		}

		throw new Exception(
			code: 404,
			message: 'No route found for path: "' . $path . '" and request method: "' . $method . '"',
		);
	}

	/**
	 * Returns the current route.
	 * This will only return something,
	 * once Router::find() has been called
	 * and only if a route was found.
	 */
	public function route(): Route|null
	{
		return $this->route;
	}
}
