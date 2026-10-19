<?php

namespace Kirby\Http;

use Closure;
use Kirby\Toolkit\Str;

/**
 * Handles Internationalized Domain Names
 *
 * @package   Kirby Http
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
class Idn
{
	/**
	 * Converts the domain of an email address and keeps
	 * the email as is if the domain cannot be converted
	 * @since 5.6.2
	 */
	protected static function convertEmail(string $email, Closure $converter): string
	{
		// the domain starts after the last `@`
		$at = strrpos($email, '@');

		if ($at === false) {
			return $email;
		}

		$domain = $converter(substr($email, $at + 1));

		return $domain !== false ? substr($email, 0, $at + 1) . $domain : $email;
	}

	/**
	 * Convert domain name from IDNA ASCII to Unicode
	 */
	public static function decode(string $domain): string|false
	{
		// `idn_to_utf8()` throws instead of failing for an empty domain
		return $domain !== '' ? idn_to_utf8($domain) : false;
	}

	/**
	 * Decodes a email address to the Unicode format
	 */
	public static function decodeEmail(string $email): string
	{
		if (Str::contains($email, 'xn--') === true) {
			return static::convertEmail($email, static::decode(...));
		}

		return $email;
	}

	/**
	 * Convert domain name to IDNA ASCII form
	 */
	public static function encode(string $domain): string|false
	{
		// `idn_to_ascii()` throws instead of failing for an empty domain
		return $domain !== '' ? idn_to_ascii($domain) : false;
	}

	/**
	 * Encodes a email address to the Punycode format
	 */
	public static function encodeEmail(string $email): string
	{
		if (mb_detect_encoding($email, 'ASCII', true) === false) {
			return static::convertEmail($email, static::encode(...));
		}

		return $email;
	}
}
