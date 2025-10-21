<?php

namespace Kirby\Cms;

use Kirby\Exception\InvalidArgumentException;
use Kirby\Http\Request;
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
	/**
	 * Returns CORS headers based on configuration
	 *
	 * @param bool $preflight Whether this is a preflight request
	 */
	public static function headers(bool $preflight = false): array
	{
		$kirby = App::instance();

		if ($kirby->isCorsEnabled() === false) {
			return [];
		}

		// get and resolve config
		$config = $kirby->option('cors', false);

		// resolve closure
		if ($config instanceof \Closure) {
			$config = $config($kirby);
		}

		// convert boolean to empty array (use defaults)
		if ($config === true) {
			$config = [];
		}

		$config = is_array($config) ? $config : [];

		$headers = [];
		$request = $kirby->request();

		// determine allowed origin
		$allowOrigin = static::allowOrigin($config, $request);

		// no origin match found
		if ($allowOrigin === null) {
			return [];
		}

		$headers['Access-Control-Allow-Origin'] = $allowOrigin;

		static::addVaryHeader($headers, $allowOrigin, $request);
		static::addCredentialsHeader($config, $headers, $allowOrigin);
		static::addExposeHeaders($config, $headers);

		// add preflight-specific headers
		if ($preflight === true) {
			static::addPreflightHeaders($config, $headers, $request);
		}

		return $headers;
	}

	/**
	 * Determines the allowed origin based on config and request
	 *
	 * @param array $config CORS configuration array
	 * @param \Kirby\Http\Request $request Current request object
	 */
	protected static function allowOrigin(array $config, Request $request): string|null
	{
		$requestOrigin = $request->header('Origin');
		$configOrigin  = $config['allowOrigin'] ?? '*';

		return match (true) {
			$configOrigin === '*' => '*',
			$requestOrigin === null => null,
			is_string($configOrigin) => strcasecmp($configOrigin, $requestOrigin) === 0 ? $requestOrigin : null,
			default => static::matchOriginFromArray($configOrigin, $requestOrigin)
		};
	}

	/**
	 * Matches the request origin against an array of allowed origins
	 *
	 * @param array $configOrigin Array of allowed origins
	 * @param string $requestOrigin Origin from the request header
	 */
	protected static function matchOriginFromArray(array $configOrigin, string $requestOrigin): string|null
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
	 * @param \Kirby\Http\Request $request Current request object
	 */
	protected static function addVaryHeader(array &$headers, string $allowOrigin, Request $request): void
	{
		// response varies by origin for non-wildcard origins
		if ($allowOrigin !== '*') {
			$currentVary = $request->header('Vary');
			$headers['Vary'] = $currentVary ?? 'Origin';
		}
	}

	/**
	 * Adds the credentials header if configured
	 *
	 * @param array $config CORS configuration array
	 * @param array $headers Headers array (passed by reference)
	 * @param string $allowOrigin Allowed origin value
	 */
	protected static function addCredentialsHeader(array $config, array &$headers, string $allowOrigin): void
	{
		$allowCredentials = $config['allowCredentials'] ?? false;

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
	 * @param array $config CORS configuration array
	 * @param array $headers Headers array (passed by reference)
	 */
	protected static function addExposeHeaders(array $config, array &$headers): void
	{
		$exposeHeaders = $config['exposeHeaders'] ?? [];

		if (empty($exposeHeaders) === false) {
			$headers['Access-Control-Expose-Headers'] = is_array($exposeHeaders)
				? implode(', ', $exposeHeaders)
				: $exposeHeaders;
		}
	}

	/**
	 * Adds preflight-specific headers
	 *
	 * @param array $config CORS configuration array
	 * @param array $headers Headers array (passed by reference)
	 * @param \Kirby\Http\Request $request Current request object
	 */
	protected static function addPreflightHeaders(array $config, array &$headers, Request $request): void
	{
		// max age
		$maxAge = $config['maxAge'] ?? null;
		if ($maxAge !== null) {
			$headers['Access-Control-Max-Age'] = (string)$maxAge;
		}

		// allowed methods
		$methods = $config['allowMethods'] ?? ['GET', 'HEAD', 'PUT', 'POST', 'DELETE', 'PATCH'];
		if (empty($methods) === false) {
			$headers['Access-Control-Allow-Methods'] = is_array($methods)
				? implode(', ', $methods)
				: $methods;
		}

		// allowed headers
		static::addAllowHeaders($config, $headers, $request);
	}

	/**
	 * Adds headers to allow custom request headers
	 *
	 * @param array $config CORS configuration array
	 * @param array $headers Headers array (passed by reference)
	 * @param \Kirby\Http\Request $request Current request object
	 */
	protected static function addAllowHeaders(array $config, array &$headers, Request $request): void
	{
		$allowHeaders = $config['allowHeaders'] ?? [];

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

			// preflight response varies by requested headers
			if (isset($headers['Vary']) === true) {
				$headers['Vary'] .= ', Access-Control-Request-Headers';
			} else {
				$headers['Vary'] = 'Access-Control-Request-Headers';
			}
		}
	}
}
