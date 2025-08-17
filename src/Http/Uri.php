<?php

namespace Kirby\Http;

use Kirby\Cms\App;
use Kirby\Exception\InvalidArgumentException;
use SensitiveParameter;
use Stringable;
use Throwable;

/**
 * Uri builder class
 *
 * @package   Kirby Http
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
class Uri implements Stringable
{
	/**
	 * Cache for the current Uri object
	 */
	public static Uri|null $current = null;

	/**
	 * The fragment after the hash
	 */
	protected string|false|null $fragment;

	/**
	 * The host address
	 */
	protected string|null $host;

	/**
	 * The optional password for basic authentication
	 */
	protected string|false|null $password;

	/**
	 * The optional list of params
	 */
	protected Params $params;

	/**
	 * The optional path
	 */
	protected Path $path;

	/**
	 * The optional port number
	 */
	protected int|false|null $port;

	/**
	 * All original properties
	 */
	protected array $props;

	/**
	 * The optional query string without leading ?
	 */
	protected Query $query;

	/**
	 * https or http
	 */
	protected string|null $scheme;

	/**
	 * Supported schemes
	 */
	protected static array $schemes = ['http', 'https', 'ftp'];

	protected bool $slash;

	/**
	 * The optional username for basic authentication
	 */
	protected string|false|null $username = null;

	/**
	 * Creates a new URI object
	 *
	 * @param array $inject Additional props to inject if a URL string is passed
	 */
	public function __construct(array|string $props = [], array $inject = [])
	{
		if (is_string($props) === true) {
			// make sure the URL parser works properly when there's a
			// colon in the string but the string is a relative URL
			if (Url::isAbsolute($props) === false) {
				$props = 'https://getkirby.com/' . $props;
				$props = parse_url($props);
				unset($props['scheme'], $props['host']);
			} else {
				$props = parse_url($props);
			}

			$props['username'] = $props['user'] ?? null;
			$props['password'] = $props['pass'] ?? null;
			$props             = [...$props, ...$inject];
		}

		// parse the path and extract params
		if (empty($props['path']) === false) {
			$props = static::parsePath($props);
		}

		$this->props = $props;
		$this->setFragment($props['fragment'] ?? null);
		$this->setHost($props['host'] ?? null);
		$this->setParams($props['params'] ?? null);
		$this->setPassword($props['password'] ?? null);
		$this->setPath($props['path'] ?? null);
		$this->setPort($props['port'] ?? null);
		$this->setQuery($props['query'] ?? null);
		$this->setScheme($props['scheme'] ?? 'http');
		$this->setSlash($props['slash'] ?? false);
		$this->setUsername($props['username'] ?? null);
	}

	/**
	 * Magic caller to access all properties
	 */
	public function __call(string $property, array $arguments = [])
	{
		return $this->$property ?? null;
	}

	/**
	 * Make sure that cloning also clones
	 * the path and query objects
	 */
	public function __clone()
	{
		$this->path   = clone $this->path;
		$this->query  = clone $this->query;
		$this->params = clone $this->params;
	}

	/**
	 * Magic getter
	 */
	public function __get(string $property)
	{
		return $this->$property ?? null;
	}

	/**
	 * Magic setter
	 */
	public function __set(string $property, $value): void
	{
		if (method_exists($this, 'set' . $property) === true) {
			$this->{'set' . $property}($value);
		}
	}

	/**
	 * Converts the URL object to string
	 */
	public function __toString(): string
	{
		try {
			return $this->toString();
		} catch (Throwable) {
			return '';
		}
	}

	/**
	 * Returns the auth details (username:password)
	 */
	public function auth(): string|null
	{
		$auth = trim($this->username . ':' . $this->password);
		return $auth !== ':' ? $auth : null;
	}

	/**
	 * Returns the base url (scheme + host)
	 * without trailing slash
	 */
	public function base(): string|null
	{
		if ($domain = $this->domain()) {
			return $this->scheme ? $this->scheme . '://' . $domain : $domain;
		}

		return null;
	}

	/**
	 * Clones the Uri object and applies optional
	 * new props.
	 */
	public function clone(array $props = []): static
	{
		$clone = clone $this;

		foreach ($props as $key => $value) {
			$clone->__set($key, $value);
		}

		return $clone;
	}

	public static function current(array $props = []): static
	{
		if (static::$current !== null) {
			return static::$current;
		}

		if ($app = App::instance(null, true)) {
			$environment = $app->environment();
		}

		$environment ??= new Environment();

		return new static($environment->requestUrl(), $props);
	}

	/**
	 * Returns the domain without scheme, path or query.
	 * Includes auth part when not empty.
	 * Includes port number when different from 80 or 443.
	 */
	public function domain(): string|null
	{
		if ($this->host === null || $this->host === '' || $this->host === '/') {
			return null;
		}

		$auth   = $this->auth();
		$domain = '';

		if ($auth !== null) {
			$domain .= $auth . '@';
		}

		$domain .= $this->host;

		if (
			$this->port !== null &&
			in_array($this->port, [80, 443], true) === false
		) {
			$domain .= ':' . $this->port;
		}

		return $domain;
	}

	public function hasFragment(): bool
	{
		return $this->fragment !== null && $this->fragment !== '';
	}

	public function hasPath(): bool
	{
		return $this->path()->isNotEmpty();
	}

	public function hasQuery(): bool
	{
		return $this->query()->isNotEmpty();
	}

	public function https(): bool
	{
		return $this->scheme() === 'https';
	}

	/**
	 * Tries to convert the internationalized host
	 * name to the human-readable UTF8 representation
	 *
	 * @return $this
	 */
	public function idn(): static
	{
		if ($this->isAbsolute() === true) {
			$host = Idn::decode($this->host);
			$this->setHost($host);
		}
		return $this;
	}

	/**
	 * Creates an Uri object for the URL to the index.php
	 * or any other executed script.
	 */
	public static function index(array $props = []): static
	{
		if ($app = App::instance(null, true)) {
			$url = $app->url('index');
		}

		$url ??= (new Environment())->baseUrl();

		return new static($url, $props);
	}

	/**
	 * Checks if the host exists
	 */
	public function isAbsolute(): bool
	{
		return $this->host !== null && $this->host !== '';
	}

	/**
	 * Returns the fragment after the hash
	 * @since 5.1.0
	 */
	public function fragment(): string|null
	{
		return $this->fragment;
	}

	/**
	 * @return $this
	 */
	public function setFragment(string|null $fragment = null): static
	{
		$this->fragment = $fragment ? ltrim($fragment, '#') : null;
		return $this;
	}

	/**
	 * @return $this
	 */
	public function setHost(string|null $host = null): static
	{
		$this->host = $host;
		return $this;
	}

	/**
	 * @return $this
	 */
	public function setParams(Params|string|array|false|null $params = null): static
	{
		// ensure that the special constructor value of `false`
		// is never passed through as it's not supported by `Params`
		if ($params === false) {
			$params = [];
		}

		$this->params = $params instanceof Params ? $params : new Params($params);
		return $this;
	}

	/**
	 * @return $this
	 */
	public function setPassword(
		#[SensitiveParameter]
		string|null $password = null
	): static {
		$this->password = $password;
		return $this;
	}

	/**
	 * @return $this
	 */
	public function setPath(Path|string|array|null $path = null): static
	{
		$this->path = $path instanceof Path ? $path : new Path($path);
		return $this;
	}

	/**
	 * @return $this
	 */
	public function setPort(int|null $port = null): static
	{
		if ($port === 0) {
			$port = null;
		}

		if ($port !== null) {
			if ($port < 1 || $port > 65535) {
				throw new InvalidArgumentException(
					message: 'Invalid port format: ' . $port
				);
			}
		}

		$this->port = $port;
		return $this;
	}

	/**
	 * @return $this
	 */
	public function setQuery(Query|string|array|null $query = null): static
	{
		$this->query = $query instanceof Query ? $query : new Query($query);
		return $this;
	}

	/**
	 * @return $this
	 */
	public function setScheme(string|null $scheme = null): static
	{
		if (
			$scheme !== null &&
			in_array($scheme, static::$schemes, true) === false
		) {
			throw new InvalidArgumentException(
				message: 'Invalid URL scheme: ' . $scheme
			);
		}

		$this->scheme = $scheme;
		return $this;
	}

	/**
	 * Set if a trailing slash should be added to
	 * the path when the URI is being built
	 *
	 * @return $this
	 */
	public function setSlash(bool $slash = false): static
	{
		$this->slash = $slash;
		return $this;
	}

	/**
	 * @return $this
	 */
	public function setUsername(string|null $username = null): static
	{
		$this->username = $username;
		return $this;
	}

	/**
	 * Converts the Url object to an array
	 */
	public function toArray(): array
	{
		$array = [];

		foreach ($this->props as $key => $value) {
			$value = $this->$key;

			if (is_object($value) === true) {
				$value = $value->toArray();
			}

			$array[$key] = $value;
		}

		return $array;
	}

	public function toJson(...$arguments): string
	{
		return json_encode($this->toArray(), ...$arguments);
	}

	/**
	 * Returns the full URL as string
	 */
	public function toString(): string
	{
		$url   = $this->base();
		$slash = true;

		if ($url === null || $url === '') {
			$url   = '/';
			$slash = false;
		}

		$path = $this->path->toString($slash) . $this->params->toString(true);

		if ($this->slash && ($path !== '' || $slash === true)) {
			$path .= '/';
		}

		$url .= $path;
		$url .= $this->query->toString(true);

		if ($this->hasFragment() === true) {
			$url .= '#' . $this->fragment();
		}

		return $url;
	}

	/**
	 * Tries to convert a URL with an internationalized host
	 * name to the machine-readable Punycode representation
	 *
	 * @return $this
	 */
	public function unIdn(): static
	{
		if ($this->isAbsolute() === true) {
			$host = Idn::encode($this->host);
			$this->setHost($host);
		}
		return $this;
	}

	/**
	 * Parses the path inside the props and extracts
	 * the params unless disabled
	 *
	 * @return array Modified props array
	 */
	protected static function parsePath(array $props): array
	{
		// extract params, the rest is the path;
		// only do this if not explicitly disabled (set to `false`)
		if (isset($props['params']) === false || $props['params'] !== false) {
			$extract           = Params::extract($props['path']);
			$props['params'] ??= $extract['params'];
			$props['path']     = $extract['path'];
			$props['slash']  ??= $extract['slash'];

			return $props;
		}

		// use the full path;
		// automatically detect the trailing slash from it if possible
		if (is_string($props['path']) === true) {
			$props['slash'] = str_ends_with($props['path'], '/') === true;
		}

		return $props;
	}
}
