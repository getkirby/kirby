<?php

namespace Kirby\Http\Router;

use Closure;
use Exception;

/**
 * @package   Kirby Http
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   MIT
 */
class Route
{
    /**
     * Accepted request method types
     * for which the route can be registered
     *
     * @var array
     */
    protected $acceptedMethods = [
        'GET',
        'HEAD',
        'POST',
        'PUT',
        'DELETE',
        'CONNECT',
        'OPTIONS',
        'TRACE',
        'PATCH'
    ];

    /**
     * Wildcards, which can be used in
     * Route patterns to make regular expressions
     * a little more human
     *
     * @var array
     */
    protected $wildcards = [
        'required' => [
            '(:num)'     => '([0-9]+)',
            '(:alpha)'   => '([a-zA-Z]+)',
            '(:any)'     => '([a-zA-Z0-9\.\-_%= \+]+)',
            '(:all)'     => '(.*)',
        ],
        'optional' => [
            '/(:num?)'   => '(?:/([0-9]+)',
            '/(:alpha?)' => '(?:/([a-zA-Z]+)',
            '/(:any?)'   => '(?:/([a-zA-Z0-9\.\-_%= ]+)',
            '/(:all?)'   => '(?:/(.*)',
        ],
    ];

    /**
     * The registered pattern(s)
     *
     * @var array
     */
    protected $pattern = [];

    /**
     * The registered request method(s)
     *
     * @var array
     */
    protected $method = [];

    /**
     * The callback action function
     *
     * @var Closure
     */
    protected $action;

    /**
     * Creates a new Route object for the given
     * pattern(s), method(s) and the callback action
     *
     * @param string|array $pattern
     * @param string|array $method
     * @param Closure      $action
     */
    public function __construct($pattern, $method = 'GET', Closure $action, array $attributes = [])
    {
        $this->pattern($pattern);
        $this->method($method);
        $this->action($action);
        $this->attributes($attributes);
    }

    /**
     * Setter and getter for the pattern
     *
     * @param  string|array|null $pattern
     * @return array
     */
    public function pattern($pattern = null): array
    {
        if ($pattern === null) {
            return $this->pattern;
        } elseif (is_string($pattern)) {
            return $this->pattern = [rtrim($pattern, '/')];
        } elseif (is_array($pattern)) {
            foreach ($pattern as $p) {
                $this->pattern[] = rtrim($p, '/');
            }
            return $this->pattern;
        } else {
            throw new Exception('Invalid pattern type');
        }
    }

    /**
     * Setter and getter for the method
     *
     * @param  string|array|null $method
     * @return array
     */
    public function method($method = null): array
    {
        if ($method === null) {
            return $this->method;
        } elseif (is_string($method)) {
            $method = strtoupper($method);

            if ($method === 'ALL') {
                return $this->method = $this->acceptedMethods;
            }

            $this->method = [];

            foreach (explode('|', $method) as $methodName) {
                $methodName = trim($methodName);
                if (in_array($methodName, $this->acceptedMethods) === true) {
                    $this->method[] = $methodName;
                } else {
                    throw new Exception('Invalid method name');
                }
            }

            return $this->method;
        } elseif (is_array($method)) {
            return $this->method = $method;
        } else {
            throw new Exception('Invalid method type');
        }
    }

    /**
     * Setter and getter for the action callback
     *
     * @param  Closure|null $action
     * @return Closure|null
     */
    public function action(Closure $action = null)
    {
        if ($action === null) {
            return $this->action;
        } else {
            return $this->action = $action;
        }
    }

    /**
     * Setter and getter for additional attributes
     *
     * @param  array|null $attributes
     * @return array|null
     */
    public function attributes(array $attributes = null)
    {
        if ($attributes === null) {
            return $this->attributes;
        } else {
            return $this->attributes = $attributes;
        }
    }

    /**
     * Converts the pattern into a full regular
     * expression by replacing all the wildcards
     *
     * @param  string $pattern
     * @return string
     */
    public function regex(string $pattern): string
    {

        $search   = array_keys($this->wildcards['optional']);
        $replace  = array_values($this->wildcards['optional']);

        // For optional parameters, first translate the wildcards to their
        // regex equivalent, sans the ")?" ending. We'll add the endings
        // back on when we know the replacement count.
        $pattern = str_replace($search, $replace, $pattern, $count);

        if ($count > 0) {
            $pattern .= str_repeat(')?', $count);
        }

        return strtr($pattern, $this->wildcards['required']);
    }

    /**
     * Tries to match the path with the regular expression and
     * extracts all arguments for the Route action
     *
     * @param  string       $pattern
     * @param  string       $path
     * @return array|false
     */
    public function arguments(string $pattern, string $path)
    {
        // check for direct matches
        if ($pattern === $path) {
            return [];
        }

        // We only need to check routes with regular expression since all others
        // would have been able to be matched by the search for literal matches
        // we just did before we started searching.
        if (strpos($pattern, '(') === false) {
            return false;
        }

        // If we have a match we'll return all results
        // from the preg without the full first match.
        if (preg_match('#^' . $this->regex($pattern) . '$#u', $path, $parameters)) {
            return array_slice($parameters, 1);
        }

        return false;
    }
}
