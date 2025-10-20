<?php

namespace Kirby\Cms;

use Kirby\Exception\InvalidArgumentException;
use Kirby\Filesystem\Mime;
use Kirby\Http\Response as HttpResponse;
use Kirby\Toolkit\Str;
use Stringable;

/**
 * Global response configuration
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class Responder implements Stringable
{
	/**
	 * Timestamp when the response expires
	 * in Kirby's cache
	 */
	protected int|null $expires = null;

	/**
	 * HTTP status code
	 */
	protected int|null $code = null;

	/**
	 * Response body
	 */
	protected string|null $body = null;

	/**
	 * Flag that defines whether the current
	 * response can be cached by Kirby's cache
	 */
	protected bool $cache = true;

	/**
	 * HTTP headers
	 */
	protected array $headers = [];

	/**
	 * Content type
	 */
	protected string|null $type = null;

	/**
	 * Flag that defines whether the current
	 * response uses the HTTP `Authorization`
	 * request header
	 */
	protected bool $usesAuth = false;

	/**
	 * List of cookie names the response
	 * relies on
	 */
	protected array $usesCookies = [];

	/**
	 * Creates and sends the response
	 */
	public function __toString(): string
	{
		return (string)$this->send();
	}

	/**
	 * Setter and getter for the response body
	 *
	 * @return $this|string|null
	 */
	public function body(string|null $body = null): static|string|null
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
	 * @return bool|$this
	 */
	public function cache(bool|null $cache = null): bool|static
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
	 * @return bool|$this
	 */
	public function usesAuth(bool|null $usesAuth = null): bool|static
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
	 */
	public function usesCookie(string $name): void
	{
		// only add unique names
		if (in_array($name, $this->usesCookies, true) === false) {
			$this->usesCookies[] = $name;
		}
	}

	/**
	 * Setter and getter for the list of cookie
	 * names the response relies on
	 * @since 3.7.0
	 *
	 * @return array|$this
	 */
	public function usesCookies(array|null $usesCookies = null)
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
				throw new InvalidArgumentException(
					message: 'Invalid time string "' . $expires . '"'
				);
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
	 * @return int|$this
	 */
	public function code(int|null $code = null)
	{
		if ($code === null) {
			return $this->code;
		}

		$this->code = $code;
		return $this;
	}

	/**
	 * Construct response from an array
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
	 * @return array|$this
	 */
	public function headers(array|null $headers = null)
	{
		if ($headers === null) {
			$injectedHeaders = [];
			$isPrivate = static::isPrivate($this->usesAuth(), $this->usesCookies());

			if ($isPrivate === true) {
				// never ever cache private responses
				$injectedHeaders['Cache-Control'] = 'no-store, private';
			}

			// inject CORS headers if enabled
			$corsHeaders = static::corsHeaders();
			if ($corsHeaders !== []) {
				$injectedHeaders = [...$injectedHeaders, ...$corsHeaders];
			}

			if ($isPrivate === false) {
				// the response is public, but it may
				// vary based on request headers
				$vary = [];

				if ($this->usesAuth() === true) {
					$vary[] = 'Authorization';
				}

				if ($this->usesCookies() !== []) {
					$vary[] = 'Cookie';
				}

				// merge Vary from CORS if present
				if (isset($injectedHeaders['Vary']) === true) {
					// split CORS Vary into individual values to avoid duplication
					$corsVaryValues = array_map('trim', explode(',', $injectedHeaders['Vary']));
					$vary = [...$vary, ...$corsVaryValues];
				}

				if ($vary !== []) {
					$injectedHeaders['Vary'] = implode(', ', $vary);
				}
			}

			// lazily inject (never override custom headers)
			return [...$injectedHeaders, ...$this->headers];
		}

		$this->headers = $headers;
		return $this;
	}

	/**
	 * Shortcut to configure a json response
	 *
	 * @return string|$this
	 */
	public function json(array|null $json = null)
	{
		if ($json !== null) {
			$this->body(json_encode($json));
		}

		return $this->type('application/json');
	}

	/**
	 * Shortcut to create a redirect response
	 *
	 * @return $this
	 */
	public function redirect(
		string|null $location = null,
		int|null $code = null
	) {
		$location = Url::to($location ?? '/');
		$location = Url::unIdn($location);

		return $this
			->header('Location', (string)$location)
			->code($code ?? 302);
	}

	/**
	 * Creates and returns the response object from the config
	 */
	public function send(HttpResponse|string|null $body = null): HttpResponse
	{
		if ($body instanceof HttpResponse) {
			// inject headers from the responder into the response
			// (only if they are not already set);
			$body->setHeaderFallbacks($this->headers());
			return $body;
		}

		if ($body !== null) {
			$this->body($body);
		}

		return new Response($this->toArray());
	}

	/**
	 * Converts the response configuration
	 * to an array
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
	 * @return string|$this
	 */
	public function type(string|null $type = null)
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
	 *
	 * @since 3.7.0
	 * @unstable
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

	/**
	 * Returns CORS headers based on configuration
	 * @since 5.2.0
	 *
	 * @param bool $preflight Whether this is a preflight request
	 */
	public static function corsHeaders(bool $preflight = false): array
	{
		$kirby = App::instance();

		if ($kirby->cors() === false) {
			return [];
		}

		$headers       = [];
		$request       = $kirby->request();
		$requestOrigin = $request->header('Origin');
		$configOrigin  = $kirby->option('cors.allowOrigin', '*');

		// find allowed origin based on config and request
		$allowOrigin = match (true) {
			$configOrigin === '*' => '*',
			$requestOrigin === null => null,
			is_string($configOrigin) => strcasecmp($configOrigin, $requestOrigin) === 0 ? $requestOrigin : null,
			default => (function () use ($configOrigin, $requestOrigin) {
				foreach ($configOrigin as $origin) {
					if (strcasecmp($origin, $requestOrigin) === 0) {
						return $requestOrigin;
					}
				}
				return null;
			})()
		};

		// no origin match found
		if ($allowOrigin === null) {
			return [];
		}

		$headers['Access-Control-Allow-Origin'] = $allowOrigin;

		// tell caches that the response varies by origin
		// when not using a wildcard origin
		if ($configOrigin !== '*') {
			$currentVary = $request->header('Vary');
			$headers['Vary'] = $currentVary ?? 'Origin';
		}

		// allow credentials if configured
		$allowCredentials = $kirby->option('cors.allowCredentials', false);
		if ($allowCredentials === true) {
			// wildcard origins cannot be used with credentials
			if ($allowOrigin === '*') {
				throw new InvalidArgumentException(
					message: 'Cannot use wildcard origin (*) with credentials'
				);
			}

			$headers['Access-Control-Allow-Credentials'] = 'true';
		}

		// expose custom headers to the client
		$exposeHeaders = $kirby->option('cors.exposeHeaders', []);
		if (empty($exposeHeaders) === false) {
			$headers['Access-Control-Expose-Headers'] = is_array($exposeHeaders)
				? implode(', ', $exposeHeaders)
				: $exposeHeaders;
		}

		// add preflight-specific headers
		if ($preflight === true) {
			$maxAge = $kirby->option('cors.maxAge');
			if ($maxAge !== null) {
				$headers['Access-Control-Max-Age'] = (string)$maxAge;
			}

			$methods = $kirby->option('cors.allowMethods', ['GET', 'HEAD', 'PUT', 'POST', 'DELETE', 'PATCH']);
			if (empty($methods) === false) {
				$headers['Access-Control-Allow-Methods'] = is_array($methods)
					? implode(', ', $methods)
					: $methods;
			}

			$allowHeaders = $kirby->option('cors.allowHeaders', []);

			// reflect request headers if not explicitly configured
			if (empty($allowHeaders) === true) {
				$requestHeaders = $request->header('Access-Control-Request-Headers');
				if ($requestHeaders !== null) {
					$allowHeaders = Str::split($requestHeaders, ',');
				}
			}

			if (empty($allowHeaders) === false) {
				$headers['Access-Control-Allow-Headers'] = is_array($allowHeaders)
					? implode(', ', $allowHeaders)
					: $allowHeaders;

				// tell caches that preflight varies by requested headers
				if (isset($headers['Vary']) === true) {
					$headers['Vary'] .= ', Access-Control-Request-Headers';
				} else {
					$headers['Vary'] = 'Access-Control-Request-Headers';
				}
			}
		}

		return $headers;
	}
}
