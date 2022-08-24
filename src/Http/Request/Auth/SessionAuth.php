<?php

namespace Kirby\Http\Request\Auth;

use Kirby\Cms\App;
use Kirby\Http\Request\Auth;
use Kirby\Session\Session;

/**
 * Authentication data using Kirby's session
 *
 * @package   Kirby Http
 * @author    Lukas Bestle <lukas@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
class SessionAuth extends Auth
{
	/**
	 * Tries to return the session object
	 */
	public function session(): Session
	{
		return App::instance()->sessionHandler()->getManually($this->data);
	}

	/**
	 * Returns the session token
	 */
	public function token(): string
	{
		return $this->data;
	}

	/**
	 * Returns the authentication type
	 */
	public function type(): string
	{
		return 'session';
	}
}
