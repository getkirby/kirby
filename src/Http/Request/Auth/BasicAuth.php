<?php

namespace Kirby\Http\Request\Auth;

use Kirby\Http\Request\Auth;
use Kirby\Toolkit\Str;

/**
 * HTTP basic authentication data
 *
 * @package   Kirby Http
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
class BasicAuth extends Auth
{
	protected string $credentials;
	protected string|null $password;
	protected string|null $username;

	public function __construct(string $data)
	{
		parent::__construct($data);

		$this->credentials = base64_decode($data);
		$this->username    = Str::before($this->credentials, ':');
		$this->password    = Str::after($this->credentials, ':');
	}

	/**
	 * Returns the entire unencoded credentials string
	 */
	public function credentials(): string
	{
		return $this->credentials;
	}

	/**
	 * Returns the password
	 */
	public function password(): string|null
	{
		return $this->password;
	}

	/**
	 * Returns the authentication type
	 */
	public function type(): string
	{
		return 'basic';
	}

	/**
	 * Returns the username
	 */
	public function username(): string|null
	{
		return $this->username;
	}
}
