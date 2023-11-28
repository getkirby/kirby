<?php

namespace Kirby\Panel;

use Kirby\Cms\File as CmsFile;
use Kirby\Cms\ModelWithContent;
use Kirby\Cms\Translation;
use Kirby\Cms\Url;
use Kirby\Filesystem\Asset;
use Kirby\Toolkit\I18n;

/**
 * Provides information about the user model for the Panel
 * @since 3.6.0
 *
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class User extends Model
{
	/**
	 * @var \Kirby\Cms\User
	 */
	protected ModelWithContent $model;

	/**
	 * Breadcrumb array
	 */
	public function breadcrumb(): array
	{
		return [
			[
				'label' => $this->model->username(),
				'link'  => $this->url(true),
			]
		];
	}

	/**
	 * Provides options for the user dropdown
	 */
	public function dropdown(array $options = []): array
	{
		$account     = $this->model->isLoggedIn();
		$i18nPrefix  = $account ? 'account' : 'user';
		$permissions = $this->options(['preview']);
		$url         = $this->url(true);
		$result      = [];

		$result[] = [
			'dialog'   => $url . '/changeName',
			'icon'     => 'title',
			'text'     => I18n::translate($i18nPrefix . '.changeName'),
			'disabled' => $this->isDisabledDropdownOption('changeName', $options, $permissions)
		];

		$result[] = '-';

		$result[] = [
			'dialog'   => $url . '/changeEmail',
			'icon'     => 'email',
			'text'     => I18n::translate('user.changeEmail'),
			'disabled' => $this->isDisabledDropdownOption('changeEmail', $options, $permissions)
		];

		$result[] = [
			'dialog'   => $url . '/changeRole',
			'icon'     => 'bolt',
			'text'     => I18n::translate('user.changeRole'),
			'disabled' => $this->isDisabledDropdownOption('changeRole', $options, $permissions)
		];

		$result[] = [
			'dialog'   => $url . '/changeLanguage',
			'icon'     => 'translate',
			'text'     => I18n::translate('user.changeLanguage'),
			'disabled' => $this->isDisabledDropdownOption('changeLanguage', $options, $permissions)
		];

		$result[] = '-';

		$result[] = [
			'dialog'   => $url . '/changePassword',
			'icon'     => 'key',
			'text'     => I18n::translate('user.changePassword'),
			'disabled' => $this->isDisabledDropdownOption('changePassword', $options, $permissions)
		];

		if ($this->model->kirby()->system()->is2FAWithTOTP() === true) {
			if ($account || $this->model->kirby()->user()->isAdmin()) {
				if ($this->model->secret('totp') !== null) {
					$result[] = [
						'dialog'   => $url . '/totp/disable',
						'icon'     => 'qr-code',
						'text'     => I18n::translate('login.totp.disable.option'),
					];
				} elseif ($account) {
					$result[] = [
						'dialog'   => $url . '/totp/enable',
						'icon'     => 'qr-code',
						'text'     => I18n::translate('login.totp.enable.option')
					];
				}
			}
		}

		$result[] = '-';

		$result[] = [
			'dialog'   => $url . '/delete',
			'icon'     => 'trash',
			'text'     => I18n::translate($i18nPrefix . '.delete'),
			'disabled' => $this->isDisabledDropdownOption('delete', $options, $permissions)
		];

		return $result;
	}

	/**
	 * Returns the setup for a dropdown option
	 * which is used in the changes dropdown
	 * for example.
	 */
	public function dropdownOption(): array
	{
		return [
			'icon' => 'user',
			'text' => $this->model->username(),
		] + parent::dropdownOption();
	}

	public function home(): string|null
	{
		if ($home = ($this->model->blueprint()->home() ?? null)) {
			$url = $this->model->toString($home);
			return Url::to($url);
		}

		return Panel::url('site');
	}

	/**
	 * Default settings for the user's Panel image
	 */
	protected function imageDefaults(): array
	{
		return array_merge(parent::imageDefaults(), [
			'back'  => 'black',
			'icon'  => 'user',
			'ratio' => '1/1',
		]);
	}

	/**
	 * Returns the image file object based on provided query
	 * @internal
	 */
	protected function imageSource(
		string|null $query = null
	): CmsFile|Asset|null {
		if ($query === null) {
			return $this->model->avatar();
		}

		return parent::imageSource($query);
	}

	/**
	 * Returns the full path without leading slash
	 */
	public function path(): string
	{
		// path to your own account
		if ($this->model->isLoggedIn() === true) {
			return 'account';
		}

		return 'users/' . $this->model->id();
	}

	/**
	 * Returns prepared data for the panel user picker
	 */
	public function pickerData(array $params = []): array
	{
		$params['text'] ??= '{{ user.username }}';

		return array_merge(parent::pickerData($params), [
			'email'    => $this->model->email(),
			'username' => $this->model->username(),
		]);
	}

	/**
	 * Returns navigation array with
	 * previous and next user
	 *
	 * @internal
	 */
	public function prevNext(): array
	{
		$user = $this->model;

		return [
			'next' => fn () => $this->toPrevNextLink($user->next(), 'username'),
			'prev' => fn () => $this->toPrevNextLink($user->prev(), 'username')
		];
	}

	/**
	 * Returns the data array for the
	 * view's component props
	 *
	 * @internal
	 */
	public function props(): array
	{
		$user    = $this->model;
		$account = $user->isLoggedIn();

		return array_merge(
			parent::props(),
			$account ? [] : $this->prevNext(),
			[
				'blueprint' => $this->model->role()->name(),
				'model' => [
					'account'  => $account,
					'avatar'   => $user->avatar()?->url(),
					'content'  => $this->content(),
					'email'    => $user->email(),
					'id'       => $user->id(),
					'language' => $this->translation()->name(),
					'link'     => $this->url(true),
					'name'     => $user->name()->toString(),
					'role'     => $user->role()->title(),
					'username' => $user->username(),
				]
			]
		);
	}

	/**
	 * Returns the Translation object
	 * for the selected Panel language
	 */
	public function translation(): Translation
	{
		$kirby = $this->model->kirby();
		$lang  = $this->model->language();
		return $kirby->translation($lang);
	}

	/**
	 * Returns the data array for
	 * this model's Panel view
	 *
	 * @internal
	 */
	public function view(): array
	{
		return [
			'breadcrumb' => $this->breadcrumb(),
			'component'  => 'k-user-view',
			'props'      => $this->props(),
			'title'      => $this->model->username(),
		];
	}
}
