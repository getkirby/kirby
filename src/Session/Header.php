<?php

namespace Kirby\Session;

use Kirby\Http\Request;
use Kirby\Toolkit\Str;

/**
 * Handles transmission of the session token via
 * the `Authorization` request header
 *
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 * @since     6.0.0
 */
class Header
{
	/**
	 * Scheme prefix used in the `Authorization` header
	 */
	protected const SCHEME = 'Session ';

	/**
	 * Returns the session token from the `Authorization`
	 * header or null if no valid header is set
	 */
	public function get(): string|null
	{
		$header = (new Request())->headers()['Authorization'] ?? null;

		// check if the header exists and uses the "Session" scheme
		if (
			$header === null ||
			Str::startsWith($header, self::SCHEME, true) !== true
		) {
			return null;
		}

		// return the part after the scheme
		return substr($header, strlen(self::SCHEME));
	}

	/**
	 * Returns the `Authorization` header value
	 * to transmit the given session token
	 */
	public function value(string $token): string
	{
		return self::SCHEME . $token;
	}
}
