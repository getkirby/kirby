<?php

namespace Kirby\Http;

use Kirby\Http\Request\Body;
use Kirby\Http\Request\Query;
use Kirby\Http\Request\Method;
use Kirby\Http\Request\Files;

/**
 * The Request class provides
 * a simple API to inspect incoming
 * requests.
 *
 * @package   Kirby Http
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   MIT
 */
class Request
{

    /**
     * The Method object is a tiny
     * wrapper around the request method
     * name, which will validate and sanitize
     * the given name and always return
     * its uppercase version.
     *
     * Examples:
     *
     * `$request->method()->name()`
     * `$request->method()->is('post')`
     *
     * @var Method
     */
    protected $method;

    /**
     * The Query object is a wrapper around
     * the URL query string, which parses the
     * string and provides a clean API to fetch
     * particular parts of the query
     *
     * Examples:
     *
     * `$request->query()->get('foo')`
     *
     * @var Query
     */
    protected $query;

    /**
     * The Body object is a wrapper around
     * the request body, which parses the contents
     * of the body and provides an API to fetch
     * particular parts of the body
     *
     * Examples:
     *
     * `$request->body()->get('foo')`
     *
     * @var Body
     */
    protected $body;

    /**
     * The Files object is a wrapper around
     * the $_FILES global. It sanitizes the
     * $_FILES array and provides an API to fetch
     * individual files by key
     *
     * Examples:
     *
     * `$request->files()->get('upload')['size']`
     * `$request->file('upload')['size']`
     *
     * @var Files
     */
    protected $files;

    /**
     * Request URL object
     *
     * @var Uri
     */
    protected $url;

    /**
     * Creates a new Request object
     * You can either pass your own request
     * data via the $options array or use
     * the data from the incoming request.
     *
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $this->method = $options['method'] ?? $_SERVER['REQUEST_METHOD'] ?? 'GET';

        if (isset($options['query']) === true) {
            $this->query = new Query($options['query']);
        }

        if (isset($options['body']) === true) {
            $this->body = new Body($options['body']);
        }

        if (isset($options['files']) === true) {
            $this->files = new Files($options['files']);
        }

        if (isset($options['url']) === true) {
            $this->url = new Uri($options['url']);
        }
    }

    /**
     * Improved var_dump output
     *
     * @return array
     */
    public function __debuginfo(): array
    {
        return [
            'body'   => $this->body(),
            'files'  => $this->files(),
            'method' => $this->method(),
            'query'  => $this->query(),
            'url'    => $this->url()->toString()
        ];
    }

    /**
     * Detects ajax requests
     *
     * @return boolean
     */
    public function ajax(): bool
    {
        return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
    }

    /**
     * Checks if the request has been made from the command line
     *
     * @return boolean
     */
    public function cli(): bool
    {
        return Server::cli();
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
     * Returns the Query object
     *
     * @return Query
     */
    public function query(): Query
    {
        return $this->query = $this->query ?? new Query();
    }

    /**
     * Returns the request input as array
     *
     * @return array
     */
    public function data(): array
    {
        return $this->is('GET') ? $this->query()->toArray() : $this->body()->toArray();
    }

    /**
     * Returns any data field from the request
     * if it exists
     *
     * @param string $key
     * @param mixed $fallback
     * @return mixed
     */
    public function get(string $key, $fallback = null)
    {
        return $this->data()[$key] ?? $fallback;
    }

    /**
     * Returns the Body object
     *
     * @return Body
     */
    public function body(): Body
    {
        return $this->body = $this->body ?? new Body();
    }

    /**
     * Returns the Files object
     *
     * @return Files
     */
    public function files(): Files
    {
        return $this->files = $this->files ?? new Files();
    }

    /**
     * Fetches a single file array
     * from the Files object by key
     *
     * @param  string $key
     * @return array|null
     */
    public function file(string $key)
    {
        return $this->files()->get($key);
    }

    /**
     * Checks if the given method name
     * matches the name of the request method.
     *
     * @param  string  $method
     * @return boolean
     */
    public function is(string $method): bool
    {
        return $this->method === $method;
    }

    /**
     * Return all headers with polyfill for
     * missing getallheaders function
     *
     * @return array
     */
    public function headers(): array
    {
        $headers = [];

        foreach ($_SERVER as $key => $value) {
            if (substr($key, 0, 5) !== 'HTTP_' && substr($key, 0, 14) !== 'REDIRECT_HTTP_') {
                continue;
            }

            // remove HTTP_
            $key = str_replace(['REDIRECT_HTTP_', 'HTTP_'], '', $key);

            // convert to lowercase
            $key = strtolower($key);

            // replace _ with spaces
            $key = str_replace('_', ' ', $key);

            // uppercase first char in each word
            $key = ucwords($key);

            // convert spaces to dashes
            $key = str_replace(' ', '-', $key);

            $headers[$key] = $value;
        }

        return $headers;
    }

    /**
     * Returns the current Uri object.
     * If you pass props you can safely modify
     * the Url with new parameters without destroying
     * the original object.
     *
     * @param array $props
     * @return Uri
     */
    public function url(array $props = null): Uri
    {
        if ($props !== null) {
            return $this->url()->clone($props);
        }

        return $this->url = $this->url ?? Uri::current();
    }
}
