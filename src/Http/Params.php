<?php

namespace Kirby\Http;

use Kirby\Toolkit\Obj;
use Kirby\Toolkit\Str;

/**
 * A wrapper around a URL params
 * that converts it into a Kirby Obj for easier
 * access of each param.
 *
 * @package   Kirby Http
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
class Params extends Obj
{
	public static string|null $separator = null;

	/**
	 * Creates a new params object
	 */
	public function __construct(array|string|null $params)
	{
		if (is_string($params) === true) {
			$params = static::extract($params)['params'];
		}

		parent::__construct($params ?? []);
	}

	/**
	 * Extract the params from a string or array
	 */
	public static function extract(string|array|null $path = null): array
	{
		if (empty($path) === true) {
			return [
				'path'   => null,
				'params' => null,
				'slash'  => false
			];
		}

		$slash = false;

		if (is_string($path) === true) {
			$slash = substr($path, -1, 1) === '/';
			$path  = Str::split($path, '/');
		}

		if (is_array($path) === true) {
			$params    = [];
			$separator = static::separator();

			foreach ($path as $index => $p) {
				if (strpos($p, $separator) === false) {
					continue;
				}

				$paramParts = Str::split($p, $separator);
				$paramKey   = $paramParts[0] ?? null;
				$paramValue = $paramParts[1] ?? null;

				if ($paramKey !== null) {
					$params[rawurldecode($paramKey)] = $paramValue !== null ? rawurldecode($paramValue) : null;
				}

				unset($path[$index]);
			}

			return [
				'path'   => $path,
				'params' => $params,
				'slash'  => $slash
			];
		}

		return [
			'path'   => null,
			'params' => null,
			'slash'  => false
		];
	}

	public function isEmpty(): bool
	{
		return empty((array)$this) === true;
	}

	public function isNotEmpty(): bool
	{
		return empty((array)$this) === false;
	}

	/**
	 * Returns the param separator according
	 * to the operating system.
	 *
	 * Unix = ':'
	 * Windows = ';'
	 */
	public static function separator(): string
	{
		if (static::$separator !== null) {
			return static::$separator;
		}

		if (DIRECTORY_SEPARATOR === '/') {
			return static::$separator = ':';
		}

		return static::$separator = ';';
	}

	/**
	 * Converts the params object to a params string
	 * which can then be used in the URL builder again
	 */
	public function toString(
		bool $leadingSlash = false,
		bool $trailingSlash = false
	): string {
		if ($this->isEmpty() === true) {
			return '';
		}

		$params    = [];
		$separator = static::separator();

		foreach ($this as $key => $value) {
			if ($value !== null && $value !== '') {
				$params[] = rawurlencode($key) . $separator . rawurlencode($value);
			}
		}

		if (empty($params) === true) {
			return '';
		}

		$params = implode('/', $params);

		$leadingSlash  = $leadingSlash  === true ? '/' : null;
		$trailingSlash = $trailingSlash === true ? '/' : null;

		return $leadingSlash . $params . $trailingSlash;
	}

	public function __toString(): string
	{
		return $this->toString();
	}
}
