<?php

namespace Kirby\Http\Request;

use Exception;

/**
 * The Method class is a tiny
 * wrapper to sanitize
 * Request methods.
 *
 * @package   Kirby Http
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   MIT
 */
class Method
{

    /**
     * Allowed request method names
     *
     * @var array
     */
    protected $allowed = [
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
     * The method name
     *
     * @var string
     */
    protected $name;

    /**
     * Creates a new request method object
     * If null is passed, the current method
     * name from the $_SERVER global will be
     * fetched.
     *
     * @param string|null $name
     */
    public function __construct(string $name = null)
    {
        if ($name === null) {
            $name = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        }

        $name = strtoupper($name);

        if (in_array($name, $this->allowed) === false) {
            throw new Exception('Unallowed request method: ' . $name);
        }

        $this->name = $name;
    }

    /**
     * Check if the method matches the given name
     *
     * @param  string  $name
     * @return boolean
     */
    public function is(string $name): bool
    {
        return $this->name === strtoupper($name);
    }

    /**
     * Returns the method name
     *
     * @return string
     */
    public function name(): string
    {
        return $this->name;
    }

    /**
     * Converts the method name to a string
     * Alias for Method::name()
     *
     * @return string
     */
    public function toString(): string
    {
        return $this->name();
    }

    /**
     * Magic string converter
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->toString();
    }
}
