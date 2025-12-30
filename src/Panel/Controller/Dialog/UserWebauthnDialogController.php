<?php

namespace Kirby\Panel\Controller\Dialog;

use Kirby\Auth\Service\Webauthn;
use Kirby\Cms\User;
use Kirby\Exception\PermissionException;
use Kirby\Panel\Ui\Dialog;

class UserWebauthnDialogController extends UserDialogController
{
	protected Webauthn $webauthn;

	public function __construct(
		User $user
	) {
		parent::__construct($user);

		// ensure user has the necessary permissions
		if (
			$this->kirby->user()->is($this->user) !== true &&
			$this->kirby->user()->isAdmin() !== true
		) {
			throw new PermissionException(
				message: 'You are not allowed to manage passkeys for this user'
			);
		}

		$this->webauthn = Webauthn::for($user);
	}

	/**
	 * Creates a new passkey and stores it
	 * inside the user secrets
	 */
	protected function create(): void
	{
		$credentials = $this->credentials();
		$credential  = $this->request->get('credential');
		$challenge   = $this->kirby->session()->pull($this->session());
		$credential  = $this->webauthn->verifyRegister(
			$credential,
			$challenge,
		);

		// inject name into new credential (incl. fallback)
		$name = $this->request->get('name', '');

		if ($name === '') {
			$name = 'Key ' . (count($credentials) + 1);
		}

		$credentials[] = [...$credential, 'name' => $name];
		$this->user->changeSecret('webauthn', $credentials);
	}

	protected function credentials(): array
	{
		return $this->user->secret('webauthn') ?? [];
	}

	/**
	 * Render Webauthn config dialog with current passkeys
	 */
	public function load(): Dialog
	{
		$credentials = $this->credentials();
		$options     = $this->webauthn->registerOptions($credentials);

		// persist the challenge to validate the response later
		$this->kirby->session()->set($this->session(), $options['challenge']);

		return new Dialog(
			component: 'k-webauthn-dialog',
			cancelButton: [
				'icon' => 'check',
				'text' => $this->i18n('confirm'),
			],
			size: 'medium',
			submitButton: false,
			credentials: $credentials,
			options: $options
		);
	}

	protected function remove(): void
	{
		$id          = $this->request->get('id');
		$credentials = $this->credentials();
		$remaining   = $this->webauthn->removeCredential($credentials, $id);

		if ($remaining === []) {
			$remaining = null;
		}

		$this->user->changeSecret('webauthn', $remaining);
	}

	/**
	 * Session name for the pending creation challenge
	 */
	protected function session(): string
	{
		return 'kirby.webauthn.' . $this->user->id();
	}

	public function submit(): true
	{
		match ($this->request->get('action')) {
			'create' => $this->create(),
			'remove' => $this->remove()
		};

		return true;
	}
}
