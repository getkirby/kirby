<?php

namespace Kirby\Cms;

use Kirby\Exception\InvalidArgumentException;
use Kirby\Toolkit\Str;

/**
 * Handles CORS (Cross-Origin Resource Sharing)
 * headers for HTTP responses
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     5.2.0
 */
class Cors
{
	protected App $kirby;
	protected array $config;

	public function __construct()
	{
		$this->kirby = App::instance();

		if ($this->kirby->isCorsEnabled() === false) {
			$this->config = [];
			return;
		}

		// get and resolve config
		$config = $this->kirby->option('cors', false);

		// resolve closure
		if ($config instanceof \Closure) {
			$config = $config($this->kirby);
		}

		// convert boolean to empty array (use defaults)
		if ($config === true) {
			$config = [];
		}

		$this->config = is_array($config) ? $config : [];
	}

	/**
	 * Returns CORS headers based on configuration
	 *
	 * @param bool $preflight Whether this is a preflight request
	 */
	public static function headers(bool $preflight = false): array
	{
		return (new static())->toArray($preflight);
	}

	/**
	 * Converts the CORS configuration to an array of headers
	 *
	 * @param bool $preflight Whether this is a preflight request
	 */
	public function toArray(bool $preflight = false): array
	{
		// empty array if CORS is disabled
		if ($this->kirby->isCorsEnabled() === false) {
			return [];
		}

		$headers = [];

		// determine allowed origin
		$allowOrigin = $this->allowOrigin();

		// no origin match found
		if ($allowOrigin === null) {
			return [];
		}

		$headers['Access-Control-Allow-Origin'] = $allowOrigin;

		$this->addVaryHeader($headers, $allowOrigin);
		$this->addCredentialsHeader($headers, $allowOrigin);
		$this->addExposeHeaders($headers);

		// add preflight-specific headers
		if ($preflight === true) {
			$this->addPreflightHeaders($headers);
		}

		return $headers;
	}

	/**
	 * Determines the allowed origin based on config and request
	 */
	protected function allowOrigin(): string|null
	{
		$requestOrigin = $this->kirby->request()->header('Origin');
		$configOrigin  = $this->config['allowOrigin'] ?? '*';

		return match (true) {
			$configOrigin === '*' => '*',
			$requestOrigin === null => null,
			is_string($configOrigin) => strcasecmp($configOrigin, $requestOrigin) === 0 ? $requestOrigin : null,
			default => $this->matchOriginFromArray($configOrigin, $requestOrigin)
		};
	}

	/**
	 * Matches the request origin against an array of allowed origins
	 *
	 * @param array $configOrigin Array of allowed origins
	 * @param string $requestOrigin Origin from the request header
	 */
	protected function matchOriginFromArray(array $configOrigin, string $requestOrigin): string|null
	{
		foreach ($configOrigin as $origin) {
			if (strcasecmp($origin, $requestOrigin) === 0) {
				return $requestOrigin;
			}
		}

		return null;
	}

	/**
	 * Adds the Vary header for cache control
	 *
	 * @param array $headers Headers array (passed by reference)
	 * @param string $allowOrigin Allowed origin value
	 */
	protected function addVaryHeader(array &$headers, string $allowOrigin): void
	{
		// response varies by origin for non-wildcard origins
		if ($allowOrigin !== '*') {
			$headers['Vary'] = 'Origin';
		}
	}

	/**
	 * Adds the credentials header if configured
	 *
	 * @param array $headers Headers array (passed by reference)
	 * @param string $allowOrigin Allowed origin value
	 */
	protected function addCredentialsHeader(array &$headers, string $allowOrigin): void
	{
		$allowCredentials = $this->config['allowCredentials'] ?? false;

		if ($allowCredentials === true) {
			// wildcard origins cannot be used with credentials
			if ($allowOrigin === '*') {
				throw new InvalidArgumentException(
					message: 'Cannot allow credentials when using wildcard origin (*)'
				);
			}

			$headers['Access-Control-Allow-Credentials'] = 'true';
		}
	}

	/**
	 * Adds headers to expose custom headers to the client
	 *
	 * @param array $headers Headers array (passed by reference)
	 */
	protected function addExposeHeaders(array &$headers): void
	{
		$exposeHeaders = $this->config['exposeHeaders'] ?? [];

		if ($this->hasConfigValue($exposeHeaders) === true) {
			$headers['Access-Control-Expose-Headers'] = is_array($exposeHeaders)
				? implode(', ', $exposeHeaders)
				: $exposeHeaders;
		}
	}

	/**
	 * Adds preflight-specific headers
	 *
	 * @param array $headers Headers array (passed by reference)
	 */
	protected function addPreflightHeaders(array &$headers): void
	{
		// max age
		$maxAge = $this->config['maxAge'] ?? null;
		if ($maxAge !== null) {
			$headers['Access-Control-Max-Age'] = (string)$maxAge;
		}

		// allowed methods
		$methods = $this->config['allowMethods'] ?? ['GET', 'HEAD', 'PUT', 'POST', 'DELETE', 'PATCH'];
		if ($this->hasConfigValue($methods) === true) {
			$headers['Access-Control-Allow-Methods'] = is_array($methods)
				? implode(', ', $methods)
				: $methods;
		}

		// allowed headers
		$this->addAllowHeaders($headers);
	}

	/**
	 * Adds headers to allow custom request headers
	 *
	 * @param array $headers Headers array (passed by reference)
	 */
	protected function addAllowHeaders(array &$headers): void
	{
		$allowHeaders = $this->config['allowHeaders'] ?? null;

		// reflect request headers only when explicitly enabled via `true`
		if ($allowHeaders === true) {
			$requestHeaders = $this->kirby->request()->header('Access-Control-Request-Headers');
			$allowHeaders    = $requestHeaders !== null ? Str::split($requestHeaders, ',') : [];
		}

		if ($this->hasConfigValue($allowHeaders) === true) {
			$headers['Access-Control-Allow-Headers'] = is_array($allowHeaders)
				? implode(', ', $allowHeaders)
				: $allowHeaders;

			// preflight response varies by requested headers
			if (isset($headers['Vary']) === true) {
				$headers['Vary'] .= ', Access-Control-Request-Headers';
			} else {
				$headers['Vary'] = 'Access-Control-Request-Headers';
			}
		}
	}

	/**
	 * Checks whether a config value contains meaningful data
	 */
	protected function hasConfigValue(mixed $input): bool
	{
		if (is_array($input) === true) {
			return $input !== [];
		}

		if (is_string($input) === true) {
			return $input !== '';
		}

		return false;
	}
}
