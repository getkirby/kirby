<?php

namespace Kirby\Cms;

use Kirby\Exception\InvalidArgumentException;
use Kirby\Filesystem\Mime;
use Kirby\Http\Response as HttpResponse;
use Kirby\Toolkit\A;
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
	 * Tracks headers that depend on the request
	 * and must not be persisted in the cache
	 */
	protected array $volatileHeaders = [];

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
		$this->volatileHeaders = $response['volatileHeaders'] ?? [];
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
			$corsHeaders = Cors::headers();
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
		$this->volatileHeaders = [];
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
		// the `cache`, `expires`, `usesAuth`, `usesCookies` and
		// `volatileHeaders` values are explicitly *not* serialized
		// as they are volatile and not to be exported
		return [
			'body'    => $this->body(),
			'code'    => $this->code(),
			'headers' => $this->headers(),
			'type'    => $this->type(),
		];
	}

	/**
	 * Converts the response configuration to an array
	 * that can safely be cached
	 *
	 * @since 5.2.0
	 */
	public function toCacheArray(): array
	{
		$response = $this->toArray();
		$volatile = $this->collectVolatileHeaders();

		if ($volatile === []) {
			return $response;
		}

		$response['headers'] = $this->stripVolatileHeaders($response['headers'], $volatile);
		return $response;
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
	 * Marks headers (or header parts) as request-dependent, so they
	 * can be subtracted before caching a response snapshot
	 *
	 * @since 5.2.0
	 */
	public function markVolatileHeader(string $name, array|null $values = null): void
	{
		$this->appendVolatileHeader($this->volatileHeaders, $name, $values);
	}

	/**
	 * Collects volatile headers from both manual configuration
	 * and automatically injected CORS headers
	 */
	protected function collectVolatileHeaders(): array
	{
		$volatile    = $this->volatileHeaders;
		$corsHeaders = Cors::headers();

		if ($corsHeaders === []) {
			return $volatile;
		}

		foreach ($corsHeaders as $name => $value) {
			if ($name === 'Vary') {
				$corsVaryValues = array_map('trim', explode(',', $value));
				$this->appendVolatileHeader($volatile, 'Vary', $corsVaryValues);
				continue;
			}

			$this->appendVolatileHeader($volatile, $name);
		}

		return $volatile;
	}

	/**
	 * Strips request-dependent headers for safe caching
	 */
	protected function stripVolatileHeaders(array $headers, array $volatile): array
	{
		foreach ($volatile as $name => $values) {
			if ($name === 'Vary' && is_array($values) === true) {
				if (isset($headers['Vary']) === false) {
					continue;
				}

				$current   = $this->normalizeVaryValues($headers['Vary']);
				$remaining = $this->removeVaryValues($current, $values);

				if ($remaining === []) {
					unset($headers['Vary']);
				} else {
					$headers['Vary'] = implode(', ', $remaining);
				}

				continue;
			}

			unset($headers[$name]);
		}

		return $headers;
	}

	/**
	 * Adds (parts of) a header to the provided volatile header list
	 */
	protected function appendVolatileHeader(array &$target, string $name, array|null $values = null): void
	{
		if ($values === null) {
			$target[$name] = null;
			return;
		}

		if (array_key_exists($name, $target) === true && $target[$name] === null) {
			return;
		}

		$values = A::map($values, static fn ($value) => strtolower(trim($value)));
		$values = A::filter($values, static fn ($value) => $value !== '');

		if ($values === []) {
			return;
		}

		$existingValues = $target[$name] ?? [];
		$target[$name] = array_values(array_unique([...$existingValues, ...$values]));
	}

	/**
	 * Normalizes a comma-separated list of Vary values
	 * into a unique array without empty entries
	 */
	protected function normalizeVaryValues(string $value): array
	{
		$values = A::map(explode(',', $value), 'trim');
		$values = A::filter($values, static fn ($entry) => $entry !== '');

		return array_values(array_unique($values));
	}

	/**
	 * Returns the Vary values with the provided entries removed
	 */
	protected function removeVaryValues(array $values, array $remove): array
	{
		$removeLower = A::map($remove, 'strtolower');

		return array_values(A::filter(
			$values,
			static fn ($value) => in_array(strtolower($value), $removeLower, true) === false
		));
	}
}
