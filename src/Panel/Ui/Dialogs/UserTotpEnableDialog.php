<?php

namespace Kirby\Panel\Ui\Dialogs;

use Kirby\Cms\User;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Image\QrCode;
use Kirby\Panel\Ui\Dialog;
use Kirby\Toolkit\I18n;
use Kirby\Toolkit\Totp;

/**
 * Manages the Panel dialog to enable TOTP auth for the current user
 * @since 4.0.0
 *
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class UserTotpEnableDialog extends Dialog
{
	public Totp $totp;
	public User $user;

	public function __construct()
	{
		parent::__construct(
			component: 'k-totp-dialog'
		);

		$this->user = $this->kirby->user();
	}

	public function props(): array
	{
		return [
			...parent::props(),
			'qr'    => $this->qr()->toSvg(size: '100%'),
			'value' => [
				'secret' => $this->totp()->secret()
			]
		];
	}

	/**
	 * Creates a QR code with a new TOTP secret for the user
	 */
	public function qr(): QrCode
	{
		$issuer = $this->kirby->site()->title();
		$label  = $this->user->email();
		$uri    = $this->totp()->uri($issuer, $label);
		return new QrCode($uri);
	}

	/**
	 * Changes the user's TOTP secret when the dialog is submitted
	 */
	public function submit(): array
	{
		$secret  = $this->request->get('secret');
		$confirm = $this->request->get('confirm');

		if ($confirm === null) {
			throw new InvalidArgumentException(
				['key' => 'login.totp.confirm.missing']
			);
		}

		if ($this->totp($secret)->verify($confirm) === false) {
			throw new InvalidArgumentException(
				['key' => 'login.totp.confirm.invalid']
			);
		}

		$this->user->changeTotp($secret);

		return [
			'message' => I18n::translate('login.totp.enable.success')
		];
	}

	public function totp(string|null $secret = null): Totp
	{
		return $this->totp ??= new Totp($secret);
	}
}
