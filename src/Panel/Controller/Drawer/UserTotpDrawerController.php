<?php

namespace Kirby\Panel\Controller\Drawer;

use Kirby\Cms\User;
use Kirby\Cms\UserRules;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Image\QrCode;
use Kirby\Panel\Ui\Drawer;
use Kirby\Toolkit\Escape;
use Kirby\Toolkit\Totp;

/**
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 * @unstable
 */
class UserTotpDrawerController extends UserCredentialDrawerController
{
	protected string|null $secret;

	public function __construct(User $user)
	{
		parent::__construct($user, 'totp');

		// ensure user has the necessary permissions
		UserRules::changeSecret($user, 'totp', null);
	}

	protected function create(): User
	{
		$secret  = $this->request->get('secret');
		$confirm = $this->request->get('confirm');

		if ($confirm === null) {
			throw new InvalidArgumentException(
				key: 'login.totp.confirm.missing'
			);
		}

		if ((new Totp($secret))->verify($confirm) === false) {
			throw new InvalidArgumentException(
				key: 'login.totp.confirm.invalid'
			);
		}

		return $this->user->changeSecret('totp', $secret);
	}

	protected function isEnabled(): bool
	{
		return $this->user->secret('totp') !== null;
	}

	public function load(): Drawer
	{
		$totp = new Totp();
		$name = $this->user->name()->or($this->user->email());

		return new Drawer(
			component: 'k-user-totp-drawer',
			icon:      'qr-code',
			title:     $this->i18n('login.challenge.totp.label'),
			isAccount: $this->isCurrentUser(),
			isEnabled: $this->isEnabled(),
			qr:        '<img src="' . $this->qr($totp)->toDataUri(size: 200) . '" />',
			uri:       $totp->uri(
				issuer: $this->user->kirby()->site()->title(),
				label: $this->user->email()
			),
			user:      Escape::html($name),
			value:     ['secret' => $totp->secret()]
		);
	}

	/**
	 * Creates a QR code with a new TOTP secret for the user
	 */
	public function qr(Totp $totp): QrCode
	{
		$issuer = $this->kirby->site()->title();
		$label  = $this->user->email();
		$uri    = $totp->uri($issuer, $label);
		return new QrCode($uri);
	}

	protected function remove(): User
	{
		$this->authorize();
		return $this->user->changeSecret('totp', null);
	}

	public function submit(): bool
	{
		$this->user = match ($action = $this->request->get('action')) {
			'create' => $this->create(),
			'remove' => $this->remove(),
			default  => throw new InvalidArgumentException(
				message: 'Invalid action: ' . $action
			)
		};

		return true;
	}
}
