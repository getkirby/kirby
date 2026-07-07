<?php

namespace Kirby\Panel\Controller\Drawer;

use Kirby\Auth\Challenge;
use Kirby\Auth\Pending;
use Kirby\Cms\User;
use Kirby\Exception\InvalidArgumentException;

/**
 * Shared base for drawers that manage a removable login credential
 * (a passkey, a TOTP secret, …)
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 * @unstable
 */
abstract class UserCredentialDrawerController extends UserDrawerController
{
	public function __construct(
		User $user,
		protected string $type
	) {
		parent::__construct($user);
	}

	/**
	 * Creates a fresh challenge, stores its secret for later
	 * verification and returns its public data for the drawer.
	 * Used to potentially authorize any altering actions.
	 */
	protected function authorization(): mixed
	{
		if ($this->isCurrentUser() === false) {
			return null;
		}

		$challenge = $this->challenge();
		$pending   = $challenge->create() ?? new Pending();

		$this->kirby->session()->set(
			'kirby.security.authorize.' . $this->user->id(),
			$pending->toArray()
		);

		return $pending->public();
	}

	/**
	 * Ensures the removal is authorized before the credential is deleted
	 *
	 * @throws \Kirby\Exception\InvalidArgumentException
	 */
	protected function authorize(): void
	{
		if ($this->isCurrentUser() === true) {
			$this->authorizeCurrentUser();
			return;
		}

		$this->authorizeAdmin();
	}

	/**
	 * Admin managing another user re-enters their own password
	 */
	protected function authorizeAdmin(): void
	{
		$password = $this->request->get('password');

		try {
			$this->kirby->user()->validatePassword($password);
		} catch (InvalidArgumentException $e) {
			// re-throw without the 401 http code: a wrong password here
			// is a validation error to show inline, not an authentication
			// failure that would log the current admin out
			throw new InvalidArgumentException(
				key:      $e->getKey(),
				data:     $e->getData(),
				fallback: $e->getMessage(),
				previous: $e
			);
		}
	}

	/**
	 * The account owner re-completes the credential's own challenge,
	 * proving they still control the factor being removed
	 */
	protected function authorizeCurrentUser(): void
	{
		$challenge = $this->challenge();
		$pending   = $this->kirby->session()->pull('kirby.security.authorize.' . $this->user->id()) ?? [];
		$pending   = Pending::from($pending);

		$input = $this->request->get('authorization');

		if ($challenge->verify($input, $pending) !== true) {
			throw new InvalidArgumentException(key: 'access.code');
		}
	}

	/**
	 * The challenge the account owner must re-complete
	 * to remove one of their credentials
	 */
	protected function challenge(): Challenge
	{
		return $this->kirby->auth()->challenges()->get(
			type: $this->type,
			user: $this->user,
			mode: '2fa'
		);
	}

	protected function isCurrentUser(): bool
	{
		return $this->kirby->user()->is($this->user);
	}
}
