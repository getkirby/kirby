<?php

namespace Kirby\Session;

use Kirby\Http\Cookie as HttpCookie;
use Kirby\Http\Url;

/**
 * Handles transmission of the session token via cookie
 *
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 * @since     6.0.0
 */
class Cookie
{
	public function __construct(
		protected string $name = 'kirby_session',
		protected string|null $domain = null
	) {
	}

	/**
	 * Getter for the cookie domain
	 */
	public function domain(): string|null
	{
		return $this->domain;
	}

	/**
	 * Returns the session token from the cookie
	 * or null if no cookie is set
	 */
	public function get(): string|null
	{
		$value = HttpCookie::get($this->name);

		if (is_string($value) === false) {
			return null;
		}

		return $value;
	}

	/**
	 * Getter for the cookie name
	 */
	public function name(): string
	{
		return $this->name;
	}

	/**
	 * Removes the session cookie
	 */
	public function remove(): void
	{
		HttpCookie::remove($this->name);
	}

	/**
	 * Transmits the given session token via cookie
	 */
	public function set(string $token, int $expiry): void
	{
		HttpCookie::set($this->name, $token, [
			'lifetime' => $expiry,
			'path'     => $this->domain ? '/' : Url::index(['host' => null, 'trailingSlash' => true]),
			'domain'   => $this->domain,
			'secure'   => Url::scheme() === 'https',
			'httpOnly' => true,
			'sameSite' => 'Lax'
		]);
	}
}
