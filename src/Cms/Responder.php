<?php

namespace Kirby\Cms;

use Kirby\Exception\InvalidArgumentException;
use Kirby\Filesystem\Mime;
use Kirby\Toolkit\Str;

/**
 * Global response configuration
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class Responder
{
	/**
	 * Timestamp when the response expires
	 * in Kirby's cache
	 *
	 * @var int|null
	 */
	protected $expires = null;

	/**
	 * HTTP status code
	 *
	 * @var int
	 */
	protected $code = null;

	/**
	 * Response body
	 *
	 * @var string
	 */
	protected $body = null;

	/**
	 * Flag that defines whether the current
	 * response can be cached by Kirby's cache
	 *
	 * @var bool
	 */
	protected $cache = true;

	/**
	 * HTTP headers
	 *
	 * @var array
	 */
	protected $headers = [];

	/**
	 * Content type
	 *
	 * @var string
	 */
	protected $type = null;

	/**
	 * Flag that defines whether the current
	 * response uses the HTTP `Authorization`
	 * request header
	 *
	 * @var bool
	 */
	protected $usesAuth = false;

	/**
	 * List of cookie names the response
	 * relies on
	 *
	 * @var array
	 */
	protected $usesCookies = [];

	/**
	 * Creates and sends the response
	 *
	 * @return string
	 */
	public function __toString(): string
	{
		return (string)$this->send();
	}

	/**
	 * Setter and getter for the response body
	 *
	 * @param string|null $body
	 * @return string|$this
	 */
	public function body(string $body = null)
	{
		if ($body === null) {
			return $this->body;
		}

		$this->body = $body;
		return $this;
	}

	/**
	 * Setter and getter for the flag that defines
	 * whether the current response can be cached
	 * by Kirby's cache
	 * @since 3.5.5
	 *
	 * @param bool|null $cache
	 * @return bool|$this
	 */
	public function cache(?bool $cache = null)
	{
		if ($cache === null) {
			// never ever cache private responses
			if (static::isPrivate($this->usesAuth(), $this->usesCookies()) === true) {
				return false;
			}

			return $this->cache;
		}

		$this->cache = $cache;
		return $this;
	}

	/**
	 * Setter and getter for the flag that defines
	 * whether the current response uses the HTTP
	 * `Authorization` request header
	 * @since 3.7.0
	 *
	 * @param bool|null $usesAuth
	 * @return bool|$this
	 */
	public function usesAuth(?bool $usesAuth = null)
	{
		if ($usesAuth === null) {
			return $this->usesAuth;
		}

		$this->usesAuth = $usesAuth;
		return $this;
	}

	/**
	 * Setter for a cookie name that is
	 * used by the response
	 * @since 3.7.0
	 *
	 * @param string $name
	 * @return void
	 */
	public function usesCookie(string $name): void
	{
		// only add unique names
		if (in_array($name, $this->usesCookies) === false) {
			$this->usesCookies[] = $name;
		}
	}

	/**
	 * Setter and getter for the list of cookie
	 * names the response relies on
	 * @since 3.7.0
	 *
	 * @param array|null $usesCookies
	 * @return array|$this
	 */
	public function usesCookies(?array $usesCookies = null)
	{
		if ($usesCookies === null) {
			return $this->usesCookies;
		}

		$this->usesCookies = $usesCookies;
		return $this;
	}

	/**
	 * Setter and getter for the cache expiry
	 * timestamp for Kirby's cache
	 * @since 3.5.5
	 *
	 * @param int|string|null $expires Timestamp, number of minutes or time string to parse
	 * @param bool $override If `true`, the already defined timestamp will be overridden
	 * @return int|null|$this
	 */
	public function expires($expires = null, bool $override = false)
	{
		// getter
		if ($expires === null && $override === false) {
			return $this->expires;
		}

		// explicit un-setter
		if ($expires === null) {
			$this->expires = null;
			return $this;
		}

		// normalize the value to an integer timestamp
		if (is_int($expires) === true && $expires < 1000000000) {
			// number of minutes
			$expires = time() + ($expires * 60);
		} elseif (is_int($expires) !== true) {
			// time string
			$parsedExpires = strtotime($expires);

			if (is_int($parsedExpires) !== true) {
				throw new InvalidArgumentException('Invalid time string "' . $expires . '"');
			}

			$expires = $parsedExpires;
		}

		// by default only ever *reduce* the cache expiry time
		if (
			$override === true ||
			$this->expires === null ||
			$expires < $this->expires
		) {
			$this->expires = $expires;
		}

		return $this;
	}

	/**
	 * Setter and getter for the status code
	 *
	 * @param int|null $code
	 * @return int|$this
	 */
	public function code(int $code = null)
	{
		if ($code === null) {
			return $this->code;
		}

		$this->code = $code;
		return $this;
	}

	/**
	 * Construct response from an array
	 *
	 * @param array $response
	 */
	public function fromArray(array $response): void
	{
		$this->body($response['body'] ?? null);
		$this->cache($response['cache'] ?? null);
		$this->code($response['code'] ?? null);
		$this->expires($response['expires'] ?? null);
		$this->headers($response['headers'] ?? null);
		$this->type($response['type'] ?? null);
		$this->usesAuth($response['usesAuth'] ?? null);
		$this->usesCookies($response['usesCookies'] ?? null);
	}

	/**
	 * Setter and getter for a single header
	 *
	 * @param string $key
	 * @param string|false|null $value
	 * @param bool $lazy If `true`, an existing header value is not overridden
	 * @return string|$this
	 */
	public function header(string $key, $value = null, bool $lazy = false)
	{
		if ($value === null) {
			return $this->headers()[$key] ?? null;
		}

		if ($value === false) {
			unset($this->headers[$key]);
			return $this;
		}

		if ($lazy === true && isset($this->headers[$key]) === true) {
			return $this;
		}

		$this->headers[$key] = $value;
		return $this;
	}

	/**
	 * Setter and getter for all headers
	 *
	 * @param array|null $headers
	 * @return array|$this
	 */
	public function headers(array $headers = null)
	{
		if ($headers === null) {
			$injectedHeaders = [];

			if (static::isPrivate($this->usesAuth(), $this->usesCookies()) === true) {
				// never ever cache private responses
				$injectedHeaders['Cache-Control'] = 'no-store, private';
			} else {
				// the response is public, but it may
				// vary based on request headers
				$vary = [];

				if ($this->usesAuth() === true) {
					$vary[] = 'Authorization';
				}

				if ($this->usesCookies() !== []) {
					$vary[] = 'Cookie';
				}

				if ($vary !== []) {
					$injectedHeaders['Vary'] = implode(', ', $vary);
				}
			}

			// lazily inject (never override custom headers)
			return array_merge($injectedHeaders, $this->headers);
		}

		$this->headers = $headers;
		return $this;
	}

	/**
	 * Shortcut to configure a json response
	 *
	 * @param array|null $json
	 * @return string|$this
	 */
	public function json(array $json = null)
	{
		if ($json !== null) {
			$this->body(json_encode($json));
		}

		return $this->type('application/json');
	}

	/**
	 * Shortcut to create a redirect response
	 *
	 * @param string|null $location
	 * @param int|null $code
	 * @return $this
	 */
	public function redirect(?string $location = null, ?int $code = null)
	{
		$location = Url::to($location ?? '/');
		$location = Url::unIdn($location);

		return $this
			->header('Location', (string)$location)
			->code($code ?? 302);
	}

	/**
	 * Creates and returns the response object from the config
	 *
	 * @param string|null $body
	 * @return \Kirby\Cms\Response
	 */
	public function send(string $body = null)
	{
		if ($body !== null) {
			$this->body($body);
		}

		return new Response($this->toArray());
	}

	/**
	 * Converts the response configuration
	 * to an array
	 *
	 * @return array
	 */
	public function toArray(): array
	{
		// the `cache`, `expires`, `usesAuth` and `usesCookies`
		// values are explicitly *not* serialized as they are
		// volatile and not to be exported
		return [
			'body'    => $this->body(),
			'code'    => $this->code(),
			'headers' => $this->headers(),
			'type'    => $this->type(),
		];
	}

	/**
	 * Setter and getter for the content type
	 *
	 * @param string|null $type
	 * @return string|$this
	 */
	public function type(string $type = null)
	{
		if ($type === null) {
			return $this->type;
		}

		if (Str::contains($type, '/') === false) {
			$type = Mime::fromExtension($type);
		}

		$this->type = $type;
		return $this;
	}

	/**
	 * Checks whether the response needs to be exempted from
	 * all caches due to using dynamic data based on auth
	 * and/or cookies; the request data only matters if it
	 * is actually used/relied on by the response
	 * @since 3.7.0
	 * @internal
	 *
	 * @param bool $usesAuth
	 * @param array $usesCookies
	 * @return bool
	 */
	public static function isPrivate(bool $usesAuth, array $usesCookies): bool
	{
		$kirby = App::instance();

		if ($usesAuth === true && $kirby->request()->hasAuth() === true) {
			return true;
		}

		foreach ($usesCookies as $cookie) {
			if (isset($_COOKIE[$cookie]) === true) {
				return true;
			}
		}

		return false;
	}
}
