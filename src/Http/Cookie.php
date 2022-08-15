<?php

namespace Kirby\Http;

use Kirby\Cms\App;
use Kirby\Toolkit\Str;

/**
 * The `Cookie` class helps you to
 * handle cookies in your projects.
 *
 * @package   Kirby Http
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
class Cookie
{
	/**
	 * Key to use for cookie signing
	 */
	public static string $key = 'KirbyHttpCookieKey';

	/**
	 * Set a new cookie
	 *
	 * <code>
	 *
	 * cookie::set('mycookie', 'hello', ['lifetime' => 60]);
	 * // expires in 1 hour
	 *
	 * </code>
	 *
	 * @param string $key The name of the cookie
	 * @param string $value The cookie content
	 * @param array $options Array of options:
	 *                       lifetime, path, domain, secure, httpOnly, sameSite
	 * @return bool true: cookie was created,
	 *              false: cookie creation failed
	 */
	public static function set(string $key, string $value, array $options = []): bool
	{
		// modify CMS caching behavior
		static::trackUsage($key);

		// extract options
		$expires  = static::lifetime($options['lifetime'] ?? 0);
		$path     = $options['path']     ?? '/';
		$domain   = $options['domain']   ?? null;
		$secure   = $options['secure']   ?? false;
		$httponly = $options['httpOnly'] ?? true;
		$samesite = $options['sameSite'] ?? 'Lax';

		// add an HMAC signature of the value
		$value = static::hmac($value) . '+' . $value;

		// store that thing in the cookie global
		$_COOKIE[$key] = $value;

		// store the cookie
		$options = compact('expires', 'path', 'domain', 'secure', 'httponly', 'samesite');
		return setcookie($key, $value, $options);
	}

	/**
	 * Calculates the lifetime for a cookie
	 *
	 * @param int $minutes Number of minutes or timestamp
	 */
	public static function lifetime(int $minutes): int
	{
		if ($minutes > 1000000000) {
			// absolute timestamp
			return $minutes;
		} elseif ($minutes > 0) {
			// minutes from now
			return time() + ($minutes * 60);
		} else {
			return 0;
		}
	}

	/**
	 * Stores a cookie forever
	 *
	 * <code>
	 *
	 * cookie::forever('mycookie', 'hello');
	 * // never expires
	 *
	 * </code>
	 *
	 * @param string $key The name of the cookie
	 * @param string $value The cookie content
	 * @param array $options Array of options:
	 *                       path, domain, secure, httpOnly
	 * @return bool true: cookie was created,
	 *              false: cookie creation failed
	 */
	public static function forever(string $key, string $value, array $options = []): bool
	{
		// 9999-12-31 if supported (lower on 32-bit servers)
		$options['lifetime'] = min(253402214400, PHP_INT_MAX);
		return static::set($key, $value, $options);
	}

	/**
	 * Get a cookie value
	 *
	 * <code>
	 *
	 * cookie::get('mycookie', 'peter');
	 * // sample output: 'hello' or if the cookie is not set 'peter'
	 *
	 * </code>
	 *
	 * @param string|null $key The name of the cookie
	 * @param string|null $default The default value, which should be returned
	 *                             if the cookie has not been found
	 * @return string|array|null The found value
	 */
	public static function get(string|null $key = null, string|null $default = null): string|array|null
	{
		if ($key === null) {
			return $_COOKIE;
		}

		// modify CMS caching behavior
		static::trackUsage($key);

		$value = $_COOKIE[$key] ?? null;
		return empty($value) ? $default : static::parse($value);
	}

	/**
	 * Checks if a cookie exists
	 */
	public static function exists(string $key): bool
	{
		return static::get($key) !== null;
	}

	/**
	 * Creates a HMAC for the cookie value
	 * Used as a cookie signature to prevent easy tampering with cookie data
	 */
	protected static function hmac(string $value): string
	{
		return hash_hmac('sha1', $value, static::$key);
	}

	/**
	 * Parses the hashed value from a cookie
	 * and tries to extract the value
	 */
	protected static function parse(string $string): string|null
	{
		// if no hash-value separator is present, we can't parse the value
		if (strpos($string, '+') === false) {
			return null;
		}

		// extract hash and value
		$hash  = Str::before($string, '+');
		$value = Str::after($string, '+');

		// if the hash or the value is missing at all return null
		// $value can be an empty string, $hash can't be!
		if ($hash === '') {
			return null;
		}

		// compare the extracted hash with the hashed value
		// don't accept value if the hash is invalid
		if (hash_equals(static::hmac($value), $hash) !== true) {
			return null;
		}

		return $value;
	}

	/**
	 * Remove a cookie
	 *
	 * <code>
	 *
	 * cookie::remove('mycookie');
	 * // mycookie is now gone
	 *
	 * </code>
	 *
	 * @param string $key The name of the cookie
	 * @return bool true: the cookie has been removed,
	 *              false: the cookie could not be removed
	 */
	public static function remove(string $key): bool
	{
		if (isset($_COOKIE[$key]) === true) {
			unset($_COOKIE[$key]);
			return setcookie($key, '', 1, '/') && setcookie($key, false);
		}

		return false;
	}

	/**
	 * Tells the CMS responder that the response relies on a cookie and
	 * its value (even if the cookie isn't set in the current request);
	 * this ensures that the response is only cached for visitors who don't
	 * have this cookie set;
	 * https://github.com/getkirby/kirby/issues/4423#issuecomment-1166300526
	 */
	protected static function trackUsage(string $key): void
	{
		// lazily request the instance for non-CMS use cases
		$kirby = App::instance(null, true);

		if ($kirby) {
			$kirby->response()->usesCookie($key);
		}
	}
}
