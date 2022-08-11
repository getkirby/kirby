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
	/**
	 * @var string
	 */
	protected $credentials;

	/**
	 * @var string
	 */
	protected $password;

	/**
	 * @var string
	 */
	protected $username;

	/**
	 * @param string $token
	 */
	public function __construct(string $data)
	{
		parent::__construct($data);

		$this->credentials = base64_decode($data);
		$this->username    = Str::before($this->credentials, ':');
		$this->password    = Str::after($this->credentials, ':');
	}

	/**
	 * Returns the entire unencoded credentials string
	 *
	 * @return string
	 */
	public function credentials(): string
	{
		return $this->credentials;
	}

	/**
	 * Returns the password
	 *
	 * @return string|null
	 */
	public function password(): ?string
	{
		return $this->password;
	}

	/**
	 * Returns the authentication type
	 *
	 * @return string
	 */
	public function type(): string
	{
		return 'basic';
	}

	/**
	 * Returns the username
	 *
	 * @return string|null
	 */
	public function username(): ?string
	{
		return $this->username;
	}
}
