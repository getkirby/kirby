<?php

namespace Kirby\Cms\Auth;

use Kirby\Cms\App;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Toolkit\Properties;

/**
 * Information container for the
 * authentication status
 * @since 3.5.1
 *
 * @package   Kirby Cms
 * @author    Lukas Bestle <lukas@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class Status
{
	use Properties;

	/**
	 * Type of the active challenge
	 *
	 * @var string|null
	 */
	protected $challenge = null;

	/**
	 * Challenge type to use as a fallback
	 * when $challenge is `null`
	 *
	 * @var string|null
	 */
	protected $challengeFallback = null;

	/**
	 * Email address of the current/pending user
	 *
	 * @var string|null
	 */
	protected $email = null;

	/**
	 * Kirby instance for user lookup
	 *
	 * @var \Kirby\Cms\App
	 */
	protected $kirby;

	/**
	 * Authentication status:
	 * `active|impersonated|pending|inactive`
	 *
	 * @var string
	 */
	protected $status;

	/**
	 * Class constructor
	 *
	 * @param array $props
	 */
	public function __construct(array $props)
	{
		$this->setProperties($props);
	}

	/**
	 * Returns the authentication status
	 *
	 * @return string
	 */
	public function __toString(): string
	{
		return $this->status();
	}

	/**
	 * Returns the type of the active challenge
	 *
	 * @param bool $automaticFallback If set to `false`, no faked challenge is returned;
	 *                                WARNING: never send the resulting `null` value to the
	 *                                user to avoid leaking whether the pending user exists
	 * @return string|null
	 */
	public function challenge(bool $automaticFallback = true): string|null
	{
		// never return a challenge type if the status doesn't match
		if ($this->status() !== 'pending') {
			return null;
		}

		if ($automaticFallback === false) {
			return $this->challenge;
		} else {
			return $this->challenge ?? $this->challengeFallback;
		}
	}

	/**
	 * Returns the email address of the current/pending user
	 *
	 * @return string|null
	 */
	public function email(): string|null
	{
		return $this->email;
	}

	/**
	 * Returns the authentication status
	 *
	 * @return string `active|impersonated|pending|inactive`
	 */
	public function status(): string
	{
		return $this->status;
	}

	/**
	 * Returns an array with all public status data
	 *
	 * @return array
	 */
	public function toArray(): array
	{
		return [
			'challenge' => $this->challenge(),
			'email'     => $this->email(),
			'status'    => $this->status()
		];
	}

	/**
	 * Returns the currently logged in user
	 *
	 * @return \Kirby\Cms\User
	 */
	public function user()
	{
		// for security, only return the user if they are
		// already logged in
		if (in_array($this->status(), ['active', 'impersonated']) !== true) {
			return null;
		}

		return $this->kirby->user($this->email());
	}

	/**
	 * Sets the type of the active challenge
	 *
	 * @param string|null $challenge
	 * @return $this
	 */
	protected function setChallenge(string|null $challenge = null)
	{
		$this->challenge = $challenge;
		return $this;
	}

	/**
	 * Sets the challenge type to use as
	 * a fallback when $challenge is `null`
	 *
	 * @param string|null $challengeFallback
	 * @return $this
	 */
	protected function setChallengeFallback(string|null $challengeFallback = null)
	{
		$this->challengeFallback = $challengeFallback;
		return $this;
	}

	/**
	 * Sets the email address of the current/pending user
	 *
	 * @param string|null $email
	 * @return $this
	 */
	protected function setEmail(string|null $email = null)
	{
		$this->email = $email;
		return $this;
	}

	/**
	 * Sets the Kirby instance for user lookup
	 *
	 * @param \Kirby\Cms\App $kirby
	 * @return $this
	 */
	protected function setKirby(App $kirby)
	{
		$this->kirby = $kirby;
		return $this;
	}

	/**
	 * Sets the authentication status
	 *
	 * @param string $status `active|impersonated|pending|inactive`
	 * @return $this
	 */
	protected function setStatus(string $status)
	{
		if (in_array($status, ['active', 'impersonated', 'pending', 'inactive']) !== true) {
			throw new InvalidArgumentException([
				'data' => ['argument' => '$props[\'status\']', 'method' => 'Status::__construct']
			]);
		}

		$this->status = $status;
		return $this;
	}
}
