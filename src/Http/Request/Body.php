<?php

namespace Kirby\Http\Request;

/**
 * The Body class parses the
 * request body and provides a nice
 * interface to get values from
 * structured bodies (json encoded or form data)
 *
 * @package   Kirby Http
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   MIT
 */
class Body
{
    use Data;

    /**
     * The raw body content
     *
     * @var string|array
     */
    protected $contents;

    /**
     * The parsed content as array
     *
     * @var array
     */
    protected $data;

    /**
     * Creates a new request body object.
     * You can pass your own array or string.
     * If null is being passed, the class will
     * fetch the body either from the $_POST global
     * or from php://input.
     *
     * @param array|string|null $contents
     */
    public function __construct($contents = null)
    {
        $this->contents = $contents;
    }

    /**
     * Fetches the raw contents for the body
     * or uses the passed contents.
     *
     * @return string|array
     */
    public function contents()
    {
        if ($this->contents === null) {
            if (empty($_POST) === false) {
                $this->contents = $_POST;
            } else {
                $this->contents = file_get_contents('php://input');
            }
        }

        return $this->contents;
    }

    /**
     * Parses the raw contents once and caches
     * the result. The parser will try to convert
     * the body with the json decoder first and
     * then run parse_str to get some results
     * if the json decoder failed.
     *
     * @return array
     */
    public function data(): array
    {
        if (is_array($this->data) === true) {
            return $this->data;
        }

        $contents = $this->contents();

        // return content which is already in array form
        if (is_array($contents) === true) {
            return $this->data = $contents;
        }

        // try to convert the body from json
        $json = json_decode($contents, true);

        if (is_array($json) === true) {
            return $this->data = $json;
        }

        if (strstr($contents, '=') !== false) {
            // try to parse the body as query string
            parse_str($contents, $parsed);

            if (is_array($parsed)) {
                return $this->data = $parsed;
            }
        }

        return $this->data = [];
    }

    /**
     * Converts the data array back
     * to a http query string
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
