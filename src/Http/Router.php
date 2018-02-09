<?php

namespace Kirby\Http;

use Exception;
use Kirby\Http\Router\Route;
use Kirby\Http\Router\Result;

/**
 * @package   Kirby Http
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   MIT
 */
class Router
{

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
     */
    public function __construct(array $routes = [])
    {
        $this->register($routes);
    }

    /**
     * Registers one or multiple routes
     *
     * @param  array|Route $route
     * @return Router
     */
    public function register($route): self
    {
        if (is_a($route, Route::class) === true) {
            foreach ($route->method() as $method) {
                foreach ($route->pattern() as $pattern) {
                    $this->routes[$method][$pattern] = $route;
                }
            }
            return $this;
        }

        if (is_array($route) === true) {
            foreach ($route as $r) {
                if (is_a($r, Route::class) === true) {
                    $this->register($r);
                } else {
                    $this->register(new Route($r['pattern'], $r['method'] ?? 'GET', $r['action'], $r));
                }
            }
            return $this;
        }

        throw new Exception('Invalid data to register routes');
    }

    /**
     * Finds a Route object by path and method
     * The Route's arguments method is used to
     * find matches and return all the found
     * arguments in the path.
     *
     * @param  string $path
     * @param  string $method
     * @return Result|null
     */
    public function find(string $path, string $method)
    {
        if (isset($this->routes[$method]) === false) {
            throw new Exception('Invalid routing method: ' . $method);
        }

        foreach ($this->routes[$method] as $pattern => $route) {
            $arguments = $route->arguments($pattern, $path);

            if ($arguments !== false) {
                return new Result($pattern, $method, $route->action(), $arguments, $route->attributes());
            }
        }

        throw new Exception('No route found for path: "' . $path . '" and request method: "' . $method . '"');
    }

    /**
     * Calls the Router by path and method.
     * This will try to find a Route object
     * and then call the Route action with
     * the appropriate arguments and a Result
     * object.
     *
     * @param  string $path
     * @param  string $method
     * @return mixed
     */
    public function call(string $path = '/', string $method = 'GET')
    {
        $result = $this->find($path, $method);
        return $result->action()->call($result, ...$result->arguments());
    }
}
