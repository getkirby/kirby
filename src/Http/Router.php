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
	 *
	 * @var \Closure
	 */
	protected $afterEach;

	/**
	 * Hook that is called before each route
	 *
	 * @var \Closure
	 */
	protected $beforeEach;

	/**
	 * Store for the current route,
	 * if one can be found
	 *
	 * @var \Kirby\Http\Route|null
	 */
	protected $route;

	/**
	 * All registered routes, sorted by
	 * their request method. This makes
	 * it faster to find the right route
	 * later.
	 *
	 * @var array
	 */
	protected $routes = [
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
	 * @param array $routes
	 * @param array<string, \Closure> $hooks Optional `beforeEach` and `afterEach` hooks
	 */
	public function __construct(array $routes = [], array $hooks = [])
	{
		$this->beforeEach = $hooks['beforeEach'] ?? null;
		$this->afterEach  = $hooks['afterEach']  ?? null;

		foreach ($routes as $props) {
			if (isset($props['pattern'], $props['action']) === false) {
				throw new InvalidArgumentException('Invalid route parameters');
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
	 *
	 * @param string $path
	 * @param string $method
	 * @param Closure|null $callback
	 * @return mixed
	 */
	public function call(string $path = null, string $method = 'GET', Closure $callback = null)
	{
		$path ??= '';
		$ignore = [];
		$result = null;
		$loop   = true;

		while ($loop === true) {
			$route = $this->find($path, $method, $ignore);

			if (is_a($this->beforeEach, 'Closure') === true) {
				($this->beforeEach)($route, $path, $method);
			}

			try {
				if ($callback) {
					$result = $callback($route);
				} else {
					$result = $route->action()->call($route, ...$route->arguments());
				}

				$loop = false;
			} catch (Exceptions\NextRouteException $e) {
				$ignore[] = $route;
			}

			if (is_a($this->afterEach, 'Closure') === true) {
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
	 *
	 * @param string|null $path
	 * @param string $method
	 * @param array $routes
	 * @param \Closure|null $callback
	 * @return mixed
	 */
	public static function execute(?string $path = null, string $method = 'GET', array $routes = [], ?Closure $callback = null)
	{
		return (new static($routes))->call($path, $method, $callback);
	}

	/**
	 * Finds a Route object by path and method
	 * The Route's arguments method is used to
	 * find matches and return all the found
	 * arguments in the path.
	 *
	 * @param string $path
	 * @param string $method
	 * @param array $ignore
	 * @return \Kirby\Http\Route|null
	 */
	public function find(string $path, string $method, array $ignore = null)
	{
		if (isset($this->routes[$method]) === false) {
			throw new InvalidArgumentException('Invalid routing method: ' . $method, 400);
		}

		// remove leading and trailing slashes
		$path = trim($path, '/');

		foreach ($this->routes[$method] as $route) {
			$arguments = $route->parse($route->pattern(), $path);

			if ($arguments !== false) {
				if (empty($ignore) === true || in_array($route, $ignore) === false) {
					return $this->route = $route;
				}
			}
		}

		throw new Exception('No route found for path: "' . $path . '" and request method: "' . $method . '"', 404);
	}

	/**
	 * Returns the current route.
	 * This will only return something,
	 * once Router::find() has been called
	 * and only if a route was found.
	 *
	 * @return \Kirby\Http\Route|null
	 */
	public function route()
	{
		return $this->route;
	}
}
