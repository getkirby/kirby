<?php

namespace Kirby\Panel\Controller\Drawer;

use Kirby\Auth\Service\Webauthn;
use Kirby\Cms\User;
use Kirby\Cms\UserRules;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Panel\Ui\Drawer;

/**
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 * @unstable
 */
class UserWebauthnDrawerController extends UserCredentialDrawerController
{
	protected Webauthn $webauthn;

	public function __construct(
		User $user
	) {
		parent::__construct($user, 'webauthn');

		// ensure user has the necessary permissions
		UserRules::changeSecret($user, 'webauthn', null);

		$this->webauthn = Webauthn::for($user);
	}

	/**
	 * Creates a new passkey and stores it
	 * inside the user secrets
	 */
	protected function create(): User
	{
		parent::create();

		$credentials = $this->credentials();
		$session     = $this->session();
		$challenge   = $this->kirby->session()->pull($session);
		$credential  = $this->request->get('credential');
		$credential  = $this->webauthn->verifyRegister(
			$credential,
			$challenge,
		);

		// inject name into new credential (incl. fallback)
		$name = $this->request->get('name', '');

		if ($name === '') {
			$name = '#' . (count($credentials) + 1);
		}

		$credentials[] = [...$credential, 'name' => $name];
		return $this->user->changeSecret('webauthn', $credentials);
	}

	protected function credentials(): array
	{
		return $this->user->secret('webauthn') ?? [];
	}

	/**
	 * Render Webauthn config drawer with current passkeys
	 */
	public function load(): Drawer
	{
		$credentials  = $this->credentials();
		$registration = $this->webauthn->registerOptions($credentials);

		// persist the challenge to validate the response later
		$this->kirby->session()->set($this->session(), $registration['challenge']);

		return new Drawer(
			component:     'k-user-webauthn-drawer',
			title:         $this->i18n('login.webauthn.label'),
			icon:          'fingerprint',
			size:          'medium',
			submitButton:  false,
			assertion:     $this->authorization(),
			credentials:   $credentials,
			isAccount:     $this->isCurrentUser(),
			registration:  $registration,
			user:          $this->user->panel()->info(),
		);
	}

	protected function remove(): User
	{
		$this->authorize();

		$id          = $this->request->get('id');
		$credentials = $this->credentials();
		$remaining   = $this->webauthn->removeCredential($credentials, $id);

		if ($remaining === []) {
			$remaining = null;
		}

		return $this->user->changeSecret('webauthn', $remaining);
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
		$this->user = match ($this->request->get('action')) {
			'create' => $this->create(),
			'remove' => $this->remove(),
			default  => throw new InvalidArgumentException(
				message: 'Invalid passkey action'
			)
		};

		return true;
	}
}
