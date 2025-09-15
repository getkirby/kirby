<?php

namespace Kirby\Http;

use Kirby\Toolkit\Obj;
use Kirby\Toolkit\Str;
use Stringable;

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
class Params extends Obj implements Stringable
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
		if ($path === null || $path === '' || $path === []) {
			return [
				'path'   => null,
				'params' => null,
				'slash'  => false
			];
		}

		$slash = false;

		if (is_string($path) === true) {
			$slash = str_ends_with($path, '/') === true;
			$path  = Str::split($path, '/');
		}

		if (is_array($path) === true) {
			$params    = [];
			$separator = static::separator();

			foreach ($path as $index => $p) {
				if (str_contains($p, $separator) === false) {
					continue;
				}

				$parts = Str::split($p, $separator);

				if ($key = $parts[0] ?? null) {
					$key = rawurldecode($key);

					if ($value = $parts[1] ?? null) {
						$value = rawurldecode($value);
					}

					$params[$key] = $value;
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
		return (array)$this === [];
	}

	public function isNotEmpty(): bool
	{
		return $this->isEmpty() === false;
	}

	/**
	 * Merges the current params with the given params
	 * @since 5.1.0
	 *
	 * @return $this
	 */
	public function merge(array|string|null $params): static
	{
		$params = new static($params);

		foreach ($params as $key => $value) {
			$this->$key = $value;
		}

		return $this;
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
		return static::$separator ??= DIRECTORY_SEPARATOR === '/' ? ':' : ';';
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
				$key      = rawurlencode($key);
				$value    = rawurlencode($value);
				$params[] = $key . $separator . $value;
			}
		}

		if ($params === []) {
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
