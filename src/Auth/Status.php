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
 * @package   Kirby Auth
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
class Status
{
	protected function __construct(
		protected App $kirby,
		protected State $state,
		protected string|null $email = null,
		protected string|null $mode = null,
		protected string|null $challenge = null,
		protected Pending|null $data = null,
		protected string|null $fallback = null
	) {
	}

	/**
	 * Status when a user is logged in
	 */
	public static function active(App $kirby, User $user): static
	{
		return new static(
			kirby: $kirby,
			state: State::Active,
			email: $user->email()
		);
	}

	/**
	 * Returns the active challenge type or the fallback if enabled
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

	public function data(): Pending|null
	{
		return $this->data;
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
			return $impersonated === true ?
				static::impersonated($kirby, $user) :
				static::active($kirby, $user);
		}

		$email = $session->get('kirby.challenge.email');

		if ($email !== null) {
			$data     = $session->get('kirby.challenge.data');
			$fallback = match ($challenges) {
				[]      => null,
				default => $challenges[array_key_last($challenges)]
			};

			return static::pending(
				kirby:     $kirby,
				email:     $email,
				mode:      $session->get('kirby.challenge.mode'),
				challenge: $session->get('kirby.challenge.type'),
				data:      Pending::from($data ?? []),
				fallback:  $fallback
			);
		}

		return static::inactive($kirby);
	}

	/**
	 * Status when a user is impersonated
	 */
	public static function impersonated(App $kirby, User $user): static
	{
		return new static(
			kirby: $kirby,
			state: State::Impersonated,
			email: $user->email()
		);
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

	public function is(State $state): bool
	{
		return $this->state === $state;
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
		string|null $email = null,
		string|null $mode = null,
		string|null $challenge = null,
		Pending|null $data = null,
		string|null $fallback = null
	): static {
		return new static(
			kirby:     $kirby,
			state:     State::Pending,
			email:     $email,
			mode:      $mode,
			challenge: $challenge,
			data:      $data,
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
			'data'      => $this->data()?->public(),
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
		if (
			$this->email === null ||
			(
				$this->is(State::Active) === false &&
				$this->is(State::Impersonated) === false
			)
		) {
			return null;
		}

		return $this->kirby->user($this->email);
	}
}
