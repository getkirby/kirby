<?php

namespace Kirby\Panel\Controller\Dropdown;

use Kirby\Cms\Find;
use Kirby\Cms\ModelWithContent;

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

		// show only for current user themselves or admins
		if (
			$this->model->isLoggedIn() === true ||
			$this->kirby->user()?->isAdmin() === true
		) {
			$options[] = [
				'drawer'   => $url . '/security',
				'icon'     => 'lock',
				'text'     => $this->i18n('security')
			];

			$options[] = '-';
		}

		$options[] = [
			'dialog'   => $url . '/delete',
			'icon'     => 'trash',
			'text'     => $this->i18n($i18nPrefix . '.delete'),
			'disabled' => $this->isDisabledOption('delete')
		];

		return $options;
	}
}
