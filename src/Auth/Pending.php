<?php

namespace Kirby\Auth;

use SensitiveParameter;

/**
 * Data storage object for a pending authentication/challenge
 *
 * @package   Kirby Auth
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
class Pending
{
	/**
	 * @param mixed|null $public Will be shared with the frontend
	 * @param mixed|null $secret Will be kept secret
	 */
	public function __construct(
		protected mixed $public = null,
		#[SensitiveParameter]
		protected mixed $secret = null
	) {
	}

	/**
	 * Creates a new Pending object from the session data array
	 */
	public static function from(array $session = []): static
	{
		return new static(
			public: $session['public'] ?? null,
			secret: $session['secret'] ?? null,
		);
	}

	/**
	 * Returns any public pending data
	 * that also will be passed to the frontend
	 */
	public function public(): mixed
	{
		return $this->public;
	}

	/**
	 * Returns any protected/secret pending data
	 */
	public function secret(): mixed
	{
		return $this->secret;
	}

	/**
	 * Returns the pending data as array
	 *
	 * @return array<'public'|'secret', mixed>
	 */
	public function toArray(): array
	{
		return [
			'public' => $this->public(),
			'secret' => $this->secret()
		];
	}
}
