<?php

namespace Kirby\Http\Request;

/**
 * The Query class helps to
 * parse and inspect URL queries
 * as part of the Request object
 *
 * @package   Kirby Http
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   MIT
 */
class Query
{
    use Data;

    /**
     * The Query data array
     *
     * @var array|null
     */
    protected $data;

    /**
     * Creates a new Query object.
     * The passed data can be an array
     * or a parsable query string. If
     * null is passed, the current Query
     * will be taken from $_GET
     *
     * @param array|string|null $data
     */
    public function __construct($data = null)
    {
        if ($data === null) {
            $this->data = $_GET;
        } elseif (is_array($data)) {
            $this->data = $data;
        } else {
            parse_str($data, $parsed);
            $this->data = $parsed;
        }
    }

    /**
     * Returns the Query data as array
     *
     * @return array
     */
    public function data(): array
    {
        return $this->data;
    }

    /**
     * Converts the query data array
     * back to a query string
     *
     * @return string
     */
    public function toString(): string
    {
        return http_build_query($this->data());
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
