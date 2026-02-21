<?php

namespace Kirby\Panel;

use Kirby\Cms\File as CmsFile;
use Kirby\Cms\ModelWithContent;
use Kirby\Cms\Translation;
use Kirby\Cms\Url;
use Kirby\Filesystem\Asset;
use Kirby\Panel\Controller\Dropdown\UserSettingsDropdownController;
use Kirby\Panel\Controller\View\UserViewController;
use Kirby\Panel\Ui\Item\UserItem;

/**
 * Provides information about the user model for the Panel
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     3.6.0
 */
class User extends Model
{
	/**
	 * @var \Kirby\Cms\User
	 */
	protected ModelWithContent $model;

	/**
	 * Provides options for the user dropdown
	 * @deprecated 6.0.0 Use `Kirby\Panel\Controller\Dropdown\FileSettingsDropdownController` instead
	 */
	public function dropdown(): array
	{
		return (new UserSettingsDropdownController($this->model))->load();
	}

	/**
	 * Returns the setup for a dropdown option
	 * which is used in the changes dropdown
	 * for example.
	 *
	 * @deprecated 5.1.4 Use the Kirby\Panel\Ui\Item\UserItem class instead
	 */
	public function dropdownOption(): array
	{
		return (new UserItem(user: $this->model))->props() + [
			'icon' => 'user'
		];
	}

	public function home(): string|null
	{
		if ($home = ($this->model->blueprint()->home() ?? null)) {
			$url = $this->model->toString($home);
			return Url::to($url);
		}

		return $this->model->kirby()->panel()->url('site');
	}

	/**
	 * Default settings for the user's Panel image
	 */
	protected function imageDefaults(): array
	{
		return [
			...parent::imageDefaults(),
			'back'  => 'black',
			'icon'  => 'user',
			'ratio' => '1/1',
		];
	}

	/**
	 * Returns the image file object based on provided query
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
		$item = new UserItem(
			user:   $this->model,
			image:  $params['image'] ?? null,
			info:   $params['info'] ?? null,
			layout: $params['layout'] ?? null,
			text:   $params['text'] ?? null,
		);

		return [
			...$item->props(),
			'email'    => $this->model->email(),
			'sortable' => true,
			'username' => $this->model->username(),
		];
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
	 * @codeCoverageIgnore
	 */
	protected function viewController(): UserViewController
	{
		return new UserViewController($this->model);
	}
}
