<?php

namespace Kirby\Http;

use Kirby\Cms\App;
use Kirby\Http\Request\Auth;
use Kirby\Http\Request\Body;
use Kirby\Http\Request\Files;
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
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
class Request
{
	public static array $authTypes = [
		'basic'   => 'Kirby\Http\Request\Auth\BasicAuth',
		'bearer'  => 'Kirby\Http\Request\Auth\BearerAuth',
		'session' => 'Kirby\Http\Request\Auth\SessionAuth',
	];

	/**
	 * The auth object if available
	 */
	protected Auth|false|null $auth = null;

	/**
	 * The Body object is a wrapper around
	 * the request body, which parses the contents
	 * of the body and provides an API to fetch
	 * particular parts of the body
	 *
	 * Examples:
	 *
	 * `$request->body()->get('foo')`
	 */
	protected Body|null $body = null;

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
	 */
	protected Files|null $files = null;

	/**
	 * The Method type
	 */
	protected string $method;

	/**
	 * All options that have been passed to
	 * the request in the constructor
	 */
	protected array $options;

	/**
	 * The Query object is a wrapper around
	 * the URL query string, which parses the
	 * string and provides a clean API to fetch
	 * particular parts of the query
	 *
	 * Examples:
	 *
	 * `$request->query()->get('foo')`
	 */
	protected Query $query;

	/**
	 * Request URL object
	 */
	protected Uri $url;

	/**
	 * Creates a new Request object
	 * You can either pass your own request
	 * data via the $options array or use
	 * the data from the incoming request.
	 */
	public function __construct(array $options = [])
	{
		$this->options = $options;
		$this->method  = $this->detectRequestMethod($options['method'] ?? null);

		if (isset($options['body']) === true) {
			$this->body = $options['body'] instanceof Body ? $options['body'] : new Body($options['body']);
		}

		if (isset($options['files']) === true) {
			$this->files = $options['files'] instanceof Files ? $options['files'] : new Files($options['files']);
		}

		if (isset($options['query']) === true) {
			$this->query = $options['query'] instanceof Query ? $options['query'] : new Query($options['query']);
		}

		if (isset($options['url']) === true) {
			$this->url = $options['url'] instanceof Uri ? $options['url'] : new Uri($options['url']);
		}
	}

	/**
	 * Improved `var_dump` output
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
	 */
	public function auth(): Auth|false|null
	{
		if ($this->auth !== null) {
			return $this->auth;
		}

		// lazily request the instance for non-CMS use cases
		$kirby = App::instance(null, true);

		// tell the CMS responder that the response relies on
		// the `Authorization` header and its value (even if
		// the header isn't set in the current request);
		// this ensures that the response is only cached for
		// unauthenticated visitors;
		// https://github.com/getkirby/kirby/issues/4423#issuecomment-1166300526
		$kirby?->response()->usesAuth(true);

		if ($auth = $this->authString()) {
			$type = Str::lower(Str::before($auth, ' '));
			$data = Str::after($auth, ' ');

			$class = static::$authTypes[$type] ?? null;
			if (!$class || class_exists($class) === false) {
				return $this->auth = false;
			}

			$object = new $class($data);

			return $this->auth = $object;
		}

		return $this->auth = false;
	}

	/**
	 * Returns the Body object
	 */
	public function body(): Body
	{
		return $this->body ??= new Body();
	}

	/**
	 * Checks if the request has been made from the command line
	 */
	public function cli(): bool
	{
		return $this->options['cli'] ?? (new Environment())->cli();
	}

	/**
	 * Returns a CSRF token if stored in a header or the query
	 */
	public function csrf(): string|null
	{
		return $this->header('x-csrf') ?? $this->query()->get('csrf');
	}

	/**
	 * Returns the request input as array
	 */
	public function data(): array
	{
		return array_merge($this->body()->toArray(), $this->query()->toArray());
	}

	/**
	 * Detect the request method from various
	 * options: given method, query string, server vars
	 */
	public function detectRequestMethod(string|null $method = null): string
	{
		// all possible methods
		$methods = ['GET', 'HEAD', 'POST', 'PUT', 'DELETE', 'CONNECT', 'OPTIONS', 'TRACE', 'PATCH'];

		// the request method can be overwritten with a header
		$methodOverride = strtoupper(Environment::getGlobally('HTTP_X_HTTP_METHOD_OVERRIDE', ''));

		if ($method === null && in_array($methodOverride, $methods) === true) {
			$method = $methodOverride;
		}

		// final chain of options to detect the method
		$method = $method ?? Environment::getGlobally('REQUEST_METHOD', 'GET');

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
	 */
	public function domain(): string
	{
		return $this->url()->domain();
	}

	/**
	 * Fetches a single file array
	 * from the Files object by key
	 */
	public function file(string $key): array|null
	{
		return $this->files()->get($key);
	}

	/**
	 * Returns the Files object
	 */
	public function files(): Files
	{
		return $this->files ??= new Files();
	}

	/**
	 * Returns any data field from the request
	 * if it exists
	 */
	public function get(string|array|null $key = null, $fallback = null)
	{
		return A::get($this->data(), $key, $fallback);
	}

	/**
	 * Returns whether the request contains
	 * the `Authorization` header
	 * @since 3.7.0
	 */
	public function hasAuth(): bool
	{
		return $this->authString() !== null;
	}

	/**
	 * Returns a header by key if it exists
	 */
	public function header(string $key, $fallback = null)
	{
		$headers = array_change_key_case($this->headers());
		return $headers[strtolower($key)] ?? $fallback;
	}

	/**
	 * Return all headers with polyfill for
	 * missing getallheaders function
	 */
	public function headers(): array
	{
		$headers = [];

		foreach (Environment::getGlobally() as $key => $value) {
			if (
				substr($key, 0, 5) !== 'HTTP_' &&
				substr($key, 0, 14) !== 'REDIRECT_HTTP_'
			) {
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
	 */
	public function is(string $method): bool
	{
		return strtoupper($this->method) === strtoupper($method);
	}

	/**
	 * Returns the request method
	 */
	public function method(): string
	{
		return $this->method;
	}

	/**
	 * Shortcut to the Params object
	 */
	public function params(): Params
	{
		return $this->url()->params();
	}

	/**
	 * Shortcut to the Path object
	 */
	public function path(): Path
	{
		return $this->url()->path();
	}

	/**
	 * Returns the Query object
	 */
	public function query(): Query
	{
		return $this->query ??= new Query();
	}

	/**
	 * Checks for a valid SSL connection
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
	 */
	public function url(array|null $props = null): Uri
	{
		if ($props !== null) {
			return $this->url()->clone($props);
		}

		return $this->url ??= Uri::current();
	}

	/**
	 * Returns the raw auth string from the `auth` option
	 * or `Authorization` header unless both are empty
	 */
	protected function authString(): string|null
	{
		// both variants need to be checked separately
		// because empty strings are treated as invalid
		// but the `??` operator wouldn't do the fallback

		$option = $this->options['auth'] ?? null;
		if (empty($option) === false) {
			return $option;
		}

		$header = $this->header('authorization');
		if (empty($header) === false) {
			return $header;
		}

		return null;
	}
}
