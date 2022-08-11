<?php

namespace Kirby\Http\Request\Auth;

use Kirby\Cms\App;
use Kirby\Http\Request\Auth;

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
	 *
	 * @return \Kirby\Session\Session
	 */
	public function session()
	{
		return App::instance()->sessionHandler()->getManually($this->data);
	}

	/**
	 * Returns the session token
	 *
	 * @return string
	 */
	public function token(): string
	{
		return $this->data;
	}

	/**
	 * Returns the authentication type
	 *
	 * @return string
	 */
	public function type(): string
	{
		return 'session';
	}
}
