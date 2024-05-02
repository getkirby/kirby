<?php

namespace Kirby\Http;

use Kirby\Toolkit\Str;

/**
 * Static URL tools
 *
 * @package   Kirby Http
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
class Url
{
	/**
	 * The base Url to build absolute Urls from
	 */
	public static string|null $home = '/';

	/**
	 * The current Uri object as string
	 */
	public static string|null $current = null;

	/**
	 * Facade for all Uri object methods
	 */
	public static function __callStatic(string $method, array $arguments)
	{
		$uri = new Uri($arguments[0] ?? static::current());
		return $uri->$method(...array_slice($arguments, 1));
	}

	/**
	 * Url Builder
	 * Actually just a factory for `new Uri($parts)`
	 */
	public static function build(
		array $parts = [],
		string|null $url = null
	): string {
		$url ??= static::current();
		$uri   = new Uri($url);
		return $uri->clone($parts)->toString();
	}

	/**
	 * Returns the current url with all bells and whistles
	 */
	public static function current(): string
	{
		return static::$current ??= static::toObject()->toString();
	}

	/**
	 * Returns the url for the current directory
	 */
	public static function currentDir(): string
	{
		return dirname(static::current());
	}

	/**
	 * Tries to fix a broken url without protocol
	 * @psalm-return ($url is null ? string|null : string)
	 */
	public static function fix(string|null $url = null): string|null
	{
		// make sure to not touch absolute urls
		if (!preg_match('!^(https|http|ftp)\:\/\/!i', $url ?? '')) {
			return 'http://' . $url;
		}

		return $url;
	}

	/**
	 * Returns the home url if defined
	 */
	public static function home(): string
	{
		return static::$home;
	}

	/**
	 * Returns the url to the executed script
	 */
	public static function index(array $props = []): string
	{
		return Uri::index($props)->toString();
	}

	/**
	 * Checks if an URL is absolute
	 */
	public static function isAbsolute(string|null $url = null): bool
	{
		// matches the following groups of URLs:
		//  //example.com/uri
		//  http://example.com/uri, https://example.com/uri, ftp://example.com/uri
		//  mailto:example@example.com, geo:49.0158,8.3239?z=11
		return
			$url !== null &&
			preg_match('!^(//|[a-z0-9+-.]+://|mailto:|tel:|geo:)!i', $url) === 1;
	}

	/**
	 * Convert a relative path into an absolute URL
	 */
	public static function makeAbsolute(string|null $path = null, string|null $home = null): string
	{
		if ($path === '' || $path === '/' || $path === null) {
			return $home ?? static::home();
		}

		if (str_starts_with($path, '#') === true) {
			return $path;
		}

		if (static::isAbsolute($path)) {
			return $path;
		}

		// build the full url
		$path   = ltrim($path, '/');
		$home ??= static::home();

		if (empty($path) === true) {
			return $home;
		}

		return $home === '/' ? '/' . $path : $home . '/' . $path;
	}

	/**
	 * Returns the path for the given url
	 */
	public static function path(
		string|array|null $url = null,
		bool $leadingSlash = false,
		bool $trailingSlash = false
	): string {
		return Url::toObject($url)
			->path()
			->toString($leadingSlash, $trailingSlash);
	}

	/**
	 * Returns the query for the given url
	 */
	public static function query(string|array|null $url = null): string
	{
		return Url::toObject($url)->query()->toString();
	}

	/**
	 * Return the last url the user has been on if detectable
	 */
	public static function last(): string
	{
		return Environment::getGlobally('HTTP_REFERER', '');
	}

	/**
	 * Shortens the Url by removing all unnecessary parts
	 */
	public static function short(
		string|null $url = null,
		int $length = 0,
		bool $base = false,
		string $rep = '…'
	): string {
		$uri = static::toObject($url);

		$uri->fragment = null;
		$uri->query    = null;
		$uri->password = null;
		$uri->port     = null;
		$uri->scheme   = null;
		$uri->username = null;

		// remove the trailing slash from the path
		$uri->slash = false;

		$url = $base ? $uri->base() : $uri->toString();
		$url = str_replace('www.', '', $url ?? '');

		return Str::short($url, $length, $rep);
	}

	/**
	 * Removes the path from the Url
	 */
	public static function stripPath(string|null $url = null): string
	{
		return static::toObject($url)->setPath(null)->toString();
	}

	/**
	 * Removes the query string from the Url
	 */
	public static function stripQuery(string|null $url = null): string
	{
		return static::toObject($url)->setQuery(null)->toString();
	}

	/**
	 * Removes the fragment (hash) from the Url
	 */
	public static function stripFragment(string|null $url = null): string
	{
		return static::toObject($url)->setFragment(null)->toString();
	}

	/**
	 * Smart resolver for internal and external urls
	 */
	public static function to(
		string|null $path = null,
		array|null $options = null
	): string {
		// make sure $path is string
		$path ??= '';

		// keep relative urls
		if (
			str_starts_with($path, './') === true ||
			str_starts_with($path, '../') === true
		) {
			return $path;
		}

		$url = static::makeAbsolute($path);

		if ($options === null) {
			return $url;
		}

		return (new Uri($url, $options))->toString();
	}

	/**
	 * Converts the Url to a Uri object
	 */
	public static function toObject(string|null $url = null): Uri
	{
		return $url === null ? Uri::current() : new Uri($url);
	}
}
