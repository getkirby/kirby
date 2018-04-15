<?php

namespace Kirby\Http\Router;

use Closure;

/**
 * @package   Kirby Http
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   MIT
 */
class Result
{

    /**
     * The found pattern
     *
     * @var string
     */
    protected $pattern;

    /**
     * The used request method
     *
     * @var string
     */
    protected $method;

    /**
     * The callback action from the Route
     *
     * @var Closure
     */
    protected $action;

    /**
     * The array of matches, found in the path
     * with a little help from the Route's regex
     *
     * @var array
     */
    protected $arguments;

    /**
     * The array of additional route attributes
     *
     * @var array
     */
    protected $attributes;

    /**
     * Creates a new Result object
     *
     * @param string  $pattern
     * @param string  $method
     * @param Closure $action
     * @param array   $arguments
     */
    public function __construct(string $pattern, string $method, Closure $action, array $arguments = [], array $attributes = [])
    {
        $this->pattern    = $pattern;
        $this->method     = $method;
        $this->action     = $action;
        $this->arguments  = $arguments;
        $this->attributes = $attributes;
    }

    /**
     * Returns the Result pattern
     *
     * @return string
     */
    public function pattern(): string
    {
        return $this->pattern;
    }

    /**
     * Returns the request method
     *
     * @return string
     */
    public function method(): string
    {
        return $this->method;
    }

    /**
     * Returns the callback action from the Route
     *
     * @return Closure
     */
    public function action(): Closure
    {
        return $this->action;
    }

    /**
     * Returns the array of arguments
     *
     * @return array
     */
    public function arguments(): array
    {
        return $this->arguments;
    }

    /**
     * Returns the array of attributes
     *
     * @return array
     */
    public function attributes(): array
    {
        return $this->attributes;
    }
}
