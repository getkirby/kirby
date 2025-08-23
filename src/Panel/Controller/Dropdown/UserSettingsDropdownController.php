<?php

namespace Kirby\Panel\Controller\Dropdown;

use Kirby\Cms\Find;
use Kirby\Cms\ModelWithContent;
use Override;

/**
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 * @unstable
 */
class UserSettingsDropdownController extends ModelSettingsDropdownController
{
	/**
	 * @param \Kirby\Cms\User $model
	 */
	public function __construct(
		protected ModelWithContent $model
	) {
		parent::__construct($model);
		$this->permissions = $this->model->panel()->options(['preview']);
	}

	public static function factory(string $id): static
	{
		return new static(model: Find::user($id));
	}

	/**
	 * Provides options for the page dropdown
	 */
	#[Override]
	public function load(): array
	{
		$account     = $this->model->isLoggedIn();
		$i18nPrefix  = $account ? 'account' : 'user';
		$url         = $this->model->panel()->url(true);
		$options     = [];

		$options[] = [
			'dialog'   => $url . '/changeName',
			'icon'     => 'title',
			'text'     => $this->i18n($i18nPrefix . '.changeName'),
			'disabled' => $this->isDisabledOption('changeName')
		];

		$options[] = '-';

		$options[] = [
			'dialog'   => $url . '/changeEmail',
			'icon'     => 'email',
			'text'     => $this->i18n('user.changeEmail'),
			'disabled' => $this->isDisabledOption('changeEmail')
		];

		$options[] = [
			'dialog'   => $url . '/changeRole',
			'icon'     => 'bolt',
			'text'     => $this->i18n('user.changeRole'),
			'disabled' => $this->isDisabledOption('changeRole') || $this->model->roles()->count() < 2
		];

		$options[] = [
			'dialog'   => $url . '/changeLanguage',
			'icon'     => 'translate',
			'text'     => $this->i18n('user.changeLanguage'),
			'disabled' => $this->isDisabledOption('changeLanguage')
		];

		$options[] = '-';

		$options[] = [
			'dialog'   => $url . '/changePassword',
			'icon'     => 'key',
			'text'     => $this->i18n('user.changePassword'),
			'disabled' => $this->isDisabledOption('changePassword')
		];

		if ($this->totpMode() === 'enable') {
			$options[] = [
				'dialog'   => $url . '/totp/enable',
				'icon'     => 'qr-code',
				'text'     => $this->i18n('login.totp.enable.option')
			];
		}

		if ($this->totpMode() === 'disable') {
			$options[] = [
				'dialog'   => $url . '/totp/disable',
				'icon'     => 'qr-code',
				'text'     => $this->i18n('login.totp.disable.option')
			];
		}

		$options[] = '-';

		$options[] = [
			'dialog'   => $url . '/delete',
			'icon'     => 'trash',
			'text'     => $this->i18n($i18nPrefix . '.delete'),
			'disabled' => $this->isDisabledOption('delete')
		];

		return $options;
	}

	public function totpMode(): string|null
	{
		if ($this->kirby->system()->is2FAWithTOTP() === false) {
			return null;
		}

		if (
			$this->model->isLoggedIn() === false &&
			$this->kirby->user()?->isAdmin() !== true
		) {
			return null;
		}

		if ($this->model->secret('totp') !== null) {
			return 'disable';
		}

		if ($this->model->isLoggedIn() === true) {
			return 'enable';
		}

		return null;
	}
}
