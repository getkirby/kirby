<?php

namespace Kirby\Http;

use Kirby\Http\Request\Auth\BasicAuth;
use Kirby\Http\Request\Auth\BearerAuth;
use Kirby\Http\Request\Body;
use Kirby\Http\Request\Files;
use Kirby\Http\Request\Method;
use Kirby\Http\Request\Query;
use Kirby\Toolkit\A;
use Kirby\Toolkit\Str;

/**
 * The `Request` class provides
 * a simple API to inspect incoming
 * requests.
 *
 * @package   Kirby Http
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier GmbH
 * @license   https://opensource.org/licenses/MIT
 */
class Request
{
    /**
     * The auth object if available
     *
     * @var BearerAuth|BasicAuth|false|null
     */
    protected $auth;

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
     * The Method type
     *
     * @var string
     */
    protected $method;

    /**
     * All options that have been passed to
     * the request in the constructor
     *
     * @var array
     */
    protected $options;

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
        $this->options = $options;
        $this->method  = $this->detectRequestMethod($options['method'] ?? null);

        if (isset($options['body']) === true) {
            $this->body = new Body($options['body']);
        }

        if (isset($options['files']) === true) {
            $this->files = new Files($options['files']);
        }

        if (isset($options['query']) === true) {
            $this->query = new Query($options['query']);
        }

        if (isset($options['url']) === true) {
            $this->url = new Uri($options['url']);
        }
    }

    /**
     * Improved `var_dump` output
     *
     * @return array
     */
    public function __debugInfo(): array
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
     * Returns the Auth object if authentication is set
     *
     * @return \Kirby\Http\Request\Auth\BasicAuth|\Kirby\Http\Request\Auth\BearerAuth|null
     */
    public function auth()
    {
        if ($this->auth !== null) {
            return $this->auth;
        }

        if ($auth = $this->options['auth'] ?? $this->header('authorization')) {
            $type  = Str::before($auth, ' ');
            $token = Str::after($auth, ' ');
            $class = 'Kirby\\Http\\Request\\Auth\\' . ucfirst($type) . 'Auth';

            if (class_exists($class) === false) {
                return $this->auth = false;
            }

            return $this->auth = new $class($token);
        }

        return $this->auth = false;
    }

    /**
     * Returns the Body object
     *
     * @return \Kirby\Http\Request\Body
     */
    public function body()
    {
        return $this->body = $this->body ?? new Body();
    }

    /**
     * Checks if the request has been made from the command line
     *
     * @return bool
     */
    public function cli(): bool
    {
        return Server::cli();
    }

    /**
     * Returns a CSRF token if stored in a header or the query
     *
     * @return string|null
     */
    public function csrf(): ?string
    {
        return $this->header('x-csrf') ?? $this->query()->get('csrf');
    }

    /**
     * Returns the request input as array
     *
     * @return array
     */
    public function data(): array
    {
        return array_merge($this->body()->toArray(), $this->query()->toArray());
    }

    /**
     * Detect the request method from various
     * options: given method, query string, server vars
     *
     * @param string $method
     * @return string
     */
    public function detectRequestMethod(string $method = null): string
    {
        // all possible methods
        $methods = ['GET', 'HEAD', 'POST', 'PUT', 'DELETE', 'CONNECT', 'OPTIONS', 'TRACE', 'PATCH'];

        // the request method can be overwritten with a header
        $methodOverride = strtoupper($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'] ?? null);

        if ($method === null && in_array($methodOverride, $methods) === true) {
            $method = $methodOverride;
        }

        // final chain of options to detect the method
        $method = $method ?? $_SERVER['REQUEST_METHOD'] ?? 'GET';

        // uppercase the shit out of it
        $method = strtoupper($method);

        // sanitize the method
        if (in_array($method, $methods) === false) {
            $method = 'GET';
        }

        return $method;
    }

    /**
     * Returns the domain
     *
     * @return string
     */
    public function domain(): string
    {
        return $this->url()->domain();
    }

    /**
     * Fetches a single file array
     * from the Files object by key
     *
     * @param string $key
     * @return array|null
     */
    public function file(string $key)
    {
        return $this->files()->get($key);
    }

    /**
     * Returns the Files object
     *
     * @return \Kirby\Cms\Files
     */
    public function files()
    {
        return $this->files = $this->files ?? new Files();
    }

    /**
     * Returns any data field from the request
     * if it exists
     *
     * @param string|null|array $key
     * @param mixed $fallback
     * @return mixed
     */
    public function get($key = null, $fallback = null)
    {
        return A::get($this->data(), $key, $fallback);
    }

    /**
     * Returns a header by key if it exists
     *
     * @param string $key
     * @param mixed $fallback
     * @return mixed
     */
    public function header(string $key, $fallback = null)
    {
        $headers = array_change_key_case($this->headers());
        return $headers[strtolower($key)] ?? $fallback;
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
     * Checks if the given method name
     * matches the name of the request method.
     *
     * @param string $method
     * @return bool
     */
    public function is(string $method): bool
    {
        return strtoupper($this->method) === strtoupper($method);
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
     * Shortcut to the Params object
     */
    public function params()
    {
        return $this->url()->params();
    }

    /**
     * Shortcut to the Path object
     */
    public function path()
    {
        return $this->url()->path();
    }

    /**
     * Returns the Query object
     *
     * @return \Kirby\Http\Request\Query
     */
    public function query()
    {
        return $this->query = $this->query ?? new Query();
    }

    /**
     * Checks for a valid SSL connection
     *
     * @return bool
     */
    public function ssl(): bool
    {
        return $this->url()->scheme() === 'https';
    }

    /**
     * Returns the current Uri object.
     * If you pass props you can safely modify
     * the Url with new parameters without destroying
     * the original object.
     *
     * @param array $props
     * @return \Kirby\Http\Uri
     */
    public function url(array $props = null)
    {
        if ($props !== null) {
            return $this->url()->clone($props);
        }

        return $this->url = $this->url ?? Uri::current();
    }
}
