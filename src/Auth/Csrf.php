<?php

namespace Kirby\Auth;

use Kirby\Cms\App;

/**
 * @package   Kirby Auth
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
class Csrf
{
	public function __construct(
		protected App $kirby
	) {
	}

	/**
	 * Returns csrf from the header
	 */
	public function fromHeader(): string|null
	{
		return $this->kirby->request()->csrf();
	}

	/**
	 * Returns either predefined csrf or the one from session
	 */
	public function fromSession(): string
	{
		$isDev    = $this->kirby->option('panel.dev', false) !== false;
		$fallback = $isDev ? 'dev' : $this->kirby->csrf();
		return $this->kirby->option('api.csrf', $fallback);
	}

	/**
	 * Returns the csrf token if it exists and if it is valid
	 */
	public function get(): string|false
	{
		$header  = $this->fromHeader();
		$session = $this->fromSession();

		// compare both tokens
		if (hash_equals($session, (string)$header) !== true) {
			return false;
		}

		return $session;
	}
}
