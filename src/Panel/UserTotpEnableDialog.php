<?php

namespace Kirby\Panel;

use Kirby\Cms\App;
use Kirby\Cms\User;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Image\QrCode;
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
class UserTotpEnableDialog
{
	public App $kirby;
	public Totp $totp;
	public User $user;

	public function __construct()
	{
		$this->kirby = App::instance();
		$this->user  = $this->kirby->user();
	}

	/**
	 * Returns the Panel dialog state when opening the dialog
	 */
	public function load(): array
	{
		return [
			'component' => 'k-totp-dialog',
			'props' => [
				'qr'    => $this->qr()->toSvg(size: '100%'),
				'value' => ['secret' => $this->secret()]
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

	public function secret(): string
	{
		return $this->totp()->secret();
	}

	/**
	 * Changes the user's TOTP secret when the dialog is submitted
	 */
	public function submit(): array
	{
		$secret  = $this->kirby->request()->get('secret');
		$confirm = $this->kirby->request()->get('confirm');

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
