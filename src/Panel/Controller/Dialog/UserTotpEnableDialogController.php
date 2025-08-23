<?php

namespace Kirby\Panel\Controller\Dialog;

use Kirby\Cms\User;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Image\QrCode;
use Kirby\Panel\Controller\DialogController;
use Kirby\Panel\Ui\Dialog;
use Kirby\Toolkit\Totp;
use Override;

/**
 * Controls the Panel dialog to enable TOTP auth for the current user
 *
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
class UserTotpEnableDialogController extends DialogController
{
	public Totp $totp;
	public User $user;

	public function __construct()
	{
		parent::__construct();
		$this->user  = $this->kirby->user();
	}

	#[Override]
	public function load(): Dialog
	{
		return new Dialog(
			component: 'k-totp-dialog',
			qr: $this->qr()->toSvg(size: '100%'),
			value: ['secret' => $this->secret()]
		);
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

	public function secret(): string
	{
		return $this->totp()->secret();
	}

	/**
	 * Changes the user's TOTP secret when the dialog is submitted
	 */
	#[Override]
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
			'message' => $this->i18n('login.totp.enable.success')
		];
	}

	public function totp(string|null $secret = null): Totp
	{
		return $this->totp ??= new Totp($secret);
	}
}
