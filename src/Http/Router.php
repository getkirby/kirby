<?php

namespace Kirby\Http;

use Exception;
use Kirby\Http\Router\Result;
use Kirby\Toolkit\DI\Singletons;

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
     * A registry for Router dependencies
     * You can set dependencies, which can
     * later be injected automatically into
     * the Route action by using type hints.
     *
     * @var Singletons
     */
    protected $dependencies;

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
        $this->dependencies = new Singletons;
        $this->register($routes);
    }

    /**
     * Registers a new dependency, to inject
     * later into the Route action.
     *
     * @param  string         $name
     * @param  string|Closure $dependency
     * @return Router
     */
    public function dependency(string $name, $dependency): self
    {
        $this->dependencies->set($name, $dependency);
        return $this;
    }

    /**
     * Registers one or multiple routes
     *
     * @param  array|Route $route
     * @return Router
     */
    public function register($route): self
    {
        if (is_a($route, 'Kirby\Http\Router\Route') === true) {
            foreach ($route->method() as $method) {
                foreach ($route->pattern() as $pattern) {
                    $this->routes[$method][$pattern] = $route;
                }
            }
            return $this;
        }

        if (is_array($route) === true) {
            foreach ($route as $p) {
                $this->register($p);
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
        return $this->dependencies->call($result->action(), $result->arguments(), $result);
    }
}
