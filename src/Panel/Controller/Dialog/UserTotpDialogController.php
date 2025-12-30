<?php

namespace Kirby\Panel\Controller\Dialog;

use Kirby\Cms\User;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\PermissionException;
use Kirby\Image\QrCode;
use Kirby\Panel\Ui\Dialog;
use Kirby\Panel\Ui\Dialog\FormDialog;
use Kirby\Toolkit\Escape;
use Kirby\Toolkit\Totp;

/**
 * Controls the Panel dialog for TOTP auth for a user
 *
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
class UserTotpDialogController extends UserDialogController
{
	public Totp $totp;

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
				message: 'You are not allowed to manage TOTP for this user'
			);
		}
	}

	protected function create(): array
	{
		$this->ensureUserIsCurrentUser();

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

		$this->user->changeSecret('totp', $secret);

		return [
			'message' => $this->i18n('login.totp.enable.success')
		];
	}

	protected function ensureUserIsCurrentUser(): void
	{
		if ($this->kirby->user()->is($this->user) === false) {
			throw new PermissionException(
				message: 'You cannot enable TOTP for this user'
			);
		}
	}

	/**
	 * Checks whether TOTP is already enabled for the user
	 */
	protected function isActive(): bool
	{
		return $this->user->secret('totp') !== null;
	}

	public function load(): Dialog
	{
		// dialog to enable TOTP
		if ($this->isActive() === false) {
			$this->ensureUserIsCurrentUser();
			$totp = new Totp();

			return new Dialog(
				component: 'k-totp-dialog',
				qr:        $this->qr($totp)->toSvg(size: '100%'),
				value:    ['secret' => $totp->secret()]
			);
		}

		// dialog to disable TOTP
		$submitBtn   = [
			'text'  => $this->i18n('disable'),
			'icon'  => 'protected',
			'theme' => 'negative'
		];

		// admins can disable TOTP for other users without
		// entering their password (but not for themselves)
		if (
			$this->kirby->user()->isAdmin() === true &&
			$this->kirby->user()->is($this->user) === false
		) {
			$name = $this->user->name()->or($this->user->email());

			return new FormDialog(
				text: $this->i18n('login.totp.disable.admin', [
					'user' => Escape::html($name)
				]),
				submitButton: $submitBtn,
			);
		}

		// everybody else
		return new FormDialog(
			fields: [
				'password' => [
					'type'     => 'password',
					'required' => true,
					'counter'  => false,
					'label'    => $this->i18n('login.totp.disable.label'),
					'help'     => $this->i18n('login.totp.disable.help'),
				]
			],
			submitButton: $submitBtn
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

	protected function remove(): array
	{
		$password = $this->request->get('password');

		try {
			if ($this->kirby->user()->is($this->user) === true) {
				$this->user->validatePassword($password);
			} elseif ($this->kirby->user()->isAdmin() === false) {
				throw new PermissionException(
					message: 'You are not allowed to disable TOTP for other users'
				);
			}

			// Remove the TOTP secret from the account
			$this->user->changeSecret('totp', null);

			return [
				'message' => $this->i18n('login.totp.disable.success')
			];

		} catch (InvalidArgumentException $e) {
			// Catch and re-throw exception so that any
			// Unauthenticated exception for incorrect passwords
			// does not trigger a logout
			throw new InvalidArgumentException(
				key: $e->getKey(),
				data: $e->getData(),
				fallback: $e->getMessage(),
				previous: $e
			);
		}
	}

	public function submit(): array
	{
		if ($this->isActive() === true) {
			return $this->remove();
		}

		return $this->create();
	}
}
