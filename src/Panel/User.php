<?php

namespace Kirby\Panel;

use Kirby\Cms\File as CmsFile;
use Kirby\Cms\ModelWithContent;
use Kirby\Cms\Translation;
use Kirby\Cms\Url;
use Kirby\Filesystem\Asset;
use Kirby\Panel\Controller\Dropdown\UserSettingsDropdownController;
use Kirby\Panel\Ui\Button\ViewButtons;
use Kirby\Panel\Ui\Item\UserItem;

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
	 * Returns header buttons which should be displayed
	 * on the user view
	 */
	public function buttons(): array
	{
		return ViewButtons::view($this)->defaults(
			'theme',
			'settings',
			'languages'
		)->render();
	}

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
			user: $this->model,
			image: $params['image'] ?? null,
			info: $params['info'] ?? null,
			layout: $params['layout'] ?? null,
			text: $params['text'] ?? null,
		);

		return [
			...$item->props(),
			'email'    => $this->model->email(),
			'sortable' => true,
			'url'      => $this->model->url(),
			'username' => $this->model->username(),
		];
	}

	/**
	 * Returns navigation array with
	 * previous and next user
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
	 * Returns the data array for the view's component props
	 */
	public function props(): array
	{
		$permissions = $this->options();

		return [
			...parent::props(),
			...$this->prevNext(),
			'avatar'            => $this->model->avatar()?->url(),
			'blueprint'         => $this->model->role()->name(),
			'canChangeEmail'    => $permissions['changeEmail'],
			'canChangeLanguage' => $permissions['changeLanguage'],
			'canChangeName'     => $permissions['changeName'],
			'canChangeRole'     => $this->model->roles()->count() > 1,
			'email'             => $this->model->email(),
			'language'          => $this->translation()->name(),
			'name'              => $this->model->name()->toString(),
			'role'              => $this->model->role()->title(),
			'username'          => $this->model->username(),
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
	 * Returns the data array for this model's Panel view
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
