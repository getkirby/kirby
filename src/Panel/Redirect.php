<?php

namespace Kirby\Panel;

use Exception;
use Throwable;

/**
 * The Redirect exception can be thrown in all Panel
 * routes to send a redirect response. It is
 * primarily used in `Panel::go($location)`
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     3.6.0
 */
class Redirect extends Exception
{
	public function __construct(
		string $location,
		int $code = 302,
		protected int|false $refresh = false,
		Throwable|null $previous = null
	) {
		parent::__construct($location, $code, $previous);
	}

	/**
	 * Returns the HTTP code for the redirect
	 */
	public function code(): int
	{
		$codes = [301, 302, 303, 307, 308];

		if (in_array($this->getCode(), $codes, true) === true) {
			return $this->getCode();
		}

		return 302;
	}

	/**
	 * Returns the URL for the redirect
	 */
	public function location(): string
	{
		return $this->getMessage();
	}

	/**
	 * Returns the refresh time in seconds
	 */
	public function refresh(): int|false
	{
		return $this->refresh;
	}
}
