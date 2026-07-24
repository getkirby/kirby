<?php

namespace Kirby\Panel\Controller\Drawer;

use Kirby\Auth\Challenge;
use Kirby\Auth\Pending;
use Kirby\Cms\User;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\PermissionException;
use Throwable;

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
			[
				...$pending->toArray(),
				// the stored code is only valid for the challenge's
				// lifetime, so a leaked session cannot reuse it forever
				'expires' => time() + $challenge->timeout()
			]
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
		$key       = 'kirby.security.authorize.' . $this->user->id();
		$session   = $this->kirby->session();
		$limits    = $this->kirby->auth()->limits();
		$email     = $this->user->email();

		// block once the shared auth rate limit is exhausted, so that the
		// secret/code cannot be brute-forced from a hijacked session
		$limits->ensure($email);

		$challenge = $this->challenge();
		$stored    = $session->get($key) ?? [];
		$input     = $this->request->get('authorization');

		// a stored code is only valid for the challenge's lifetime
		$expires = $stored['expires'] ?? null;

		if (is_int($expires) === true && $expires < time()) {
			$session->remove($key);
			throw new InvalidArgumentException(key: 'access.code');
		}

		try {
			if ($challenge->verify($input, Pending::from($stored)) !== true) {
				throw new InvalidArgumentException(key: 'access.code');
			}
		} catch (Throwable $e) {
			// count the failed attempt against the rate limit
			$limits->track($email, triggerHook: false);

			// a single-use challenge signs a one-time nonce that must be
			// invalidated even after a failed attempt (e.g. WebAuthn); a
			// reusable code is kept so the account owner can retry
			// with the correct code within its lifetime
			// instead of being locked out by a single typo
			if ($challenge->isSingleUse() === true) {
				$session->remove($key);
			}

			throw $e;
		}

		// the action is authorized: consume the pending so the same
		// code or nonce cannot be replayed for another change
		$session->remove($key);
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

	protected function create(): User
	{
		if ($this->isCurrentUser() === false) {
			throw new PermissionException(
				message: 'You cannot add a login credential for ' . $this->user->email()
			);
		}

		return $this->user;
	}

	protected function isCurrentUser(): bool
	{
		return $this->kirby->user()->is($this->user);
	}
}
