<?php

namespace Kirby\Auth;

use Kirby\Cms\App;
use Kirby\Cms\User;
use Kirby\Session\Session;

/**
 * Immutable authentication status value object
 *
 * Designed to avoid ad-hoc arrays and keep the
 * status surface consistent across auth flows.
 *
 * @since 6.0.0
 */
class Status
{
	protected function __construct(
		protected App $kirby,
		protected State $state,
		protected string|null $email = null,
		protected string|null $mode = null,
		protected string|null $challenge = null,
		protected string|null $fallback = null,
	) {
	}

	/**
	 * Status when a user is logged in
	 */
	public static function active(
		App $kirby,
		User $user,
		bool $impersonated = false
	): static {
		return new static(
			kirby: $kirby,
			state: $impersonated ? State::Impersonated : State::Active,
			email: $user->email()
		);
	}

	/**
	 * Returns the active challenge or the fallback if enabled
	 */
	public function challenge(bool $fallback = true): string|null
	{
		if ($this->state !== State::Pending) {
			return null;
		}

		if ($fallback === false) {
			return $this->challenge;
		}

		return $this->challenge ?? $this->fallback;
	}

	public function email(): string|null
	{
		return $this->email;
	}

	/**
	 * Build a status object from the current auth context
	 */
	public static function for(
		App $kirby,
		User|null $user,
		bool $impersonated,
		Session $session,
		array $challenges
	): static {
		if ($user !== null) {
			return static::active(
				kirby: $kirby,
				user: $user,
				impersonated: $impersonated
			);
		}

		$email = $session->get('kirby.challenge.email');

		if ($email !== null) {
			return static::pending(
				kirby: $kirby,
				email: $email,
				mode: $session->get('kirby.challenge.mode'),
				challenge: $session->get('kirby.challenge.type'),
				fallback: $challenges !== [] ? end($challenges) : null
			);
		}

		return static::inactive($kirby);
	}

	/**
	 * Status when no auth is active
	 */
	public static function inactive(App $kirby): static
	{
		return new static(
			kirby: $kirby,
			state: State::Inactive
		);
	}

	public function mode(): string|null
	{
		return $this->mode;
	}

	/**
	 * Status when a challenge is pending
	 */
	public static function pending(
		App $kirby,
		string $email,
		string|null $mode,
		string|null $challenge,
		string|null $fallback
	): static {
		return new static(
			kirby:     $kirby,
			state:     State::Pending,
			email:     $email,
			mode:      $mode,
			challenge: $challenge,
			fallback:  $fallback
		);
	}

	public function state(): State
	{
		return $this->state;
	}

	public function toArray(): array
	{
		return [
			'challenge' => $this->challenge(),
			'email'     => $this->email(),
			'mode'      => $this->mode(),
			'status'    => $this->state()->value
		];
	}

	/**
	 * Returns the user only when authenticated
	 */
	public function user(): User|null
	{
		$authenticated = [State::Active, State::Impersonated];

		if (
			in_array($this->state, $authenticated, true) !== true ||
			$this->email === null
		) {
			return null;
		}

		return $this->kirby->user($this->email);
	}
}
