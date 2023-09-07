<?php

namespace Kirby\Cms\Auth;

use Kirby\Cms\App;
use Kirby\Cms\User;
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
	/**
	 * Type of the active challenge
	 */
	protected string|null $challenge = null;

	/**
	 * Challenge type to use as a fallback
	 * when $challenge is `null`
	 */
	protected string|null $challengeFallback = null;

	/**
	 * Email address of the current/pending user
	 */
	protected string|null $email;

	/**
	 * Kirby instance for user lookup
	 */
	protected App $kirby;

	/**
	 * Authentication status:
	 * `active|impersonated|pending|inactive`
	 */
	protected string $status;

	/**
	 * Class constructor
	 *
	 * @param array $props
	 */
	public function __construct(array $props)
	{
		if (in_array($props['status'], ['active', 'impersonated', 'pending', 'inactive']) !== true) {
			throw new InvalidArgumentException([
				'data' => [
					'argument' => '$props[\'status\']',
					'method'   => 'Status::__construct'
				]
			]);
		}

		$this->kirby 		 	 = $props['kirby'];
		$this->challenge 		 = $props['challenge'] ?? null;
		$this->challengeFallback = $props['challengeFallback'] ?? null;
		$this->email 		 	 = $props['email'] ?? null;
		$this->status 			 = $props['status'];
	}

	/**
	 * Returns the authentication status
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
	 */
	public function challenge(bool $automaticFallback = true): string|null
	{
		// never return a challenge type if the status doesn't match
		if ($this->status() !== 'pending') {
			return null;
		}

		if ($automaticFallback === false) {
			return $this->challenge;
		}

		return $this->challenge ?? $this->challengeFallback;
	}

	/**
	 * Creates a new instance while
	 * merging initial and new properties
	 */
	public function clone(array $props = []): static
	{
		return new static(array_replace_recursive([
			'kirby' 			=> $this->kirby,
			'challenge' 		=> $this->challenge,
			'challengeFallback' => $this->challengeFallback,
			'email' 			=> $this->email,
			'status' 			=> $this->status,
		], $props));
	}

	/**
	 * Returns the email address of the current/pending user
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
	 */
	public function user(): User|null
	{
		// for security, only return the user if they are
		// already logged in
		if (in_array($this->status(), ['active', 'impersonated']) !== true) {
			return null;
		}

		return $this->kirby->user($this->email());
	}
}
