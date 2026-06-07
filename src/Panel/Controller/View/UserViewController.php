<?php

namespace Kirby\Panel\Controller\View;

use Kirby\Cms\Find;
use Kirby\Cms\User;
use Kirby\Cms\Users;
use Kirby\Panel\Ui\Button\ViewButtons;

/**
 * Controls the user view
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 *
 * @extends \Kirby\Panel\Controller\View\ModelViewController<\Kirby\Cms\User, \Kirby\Panel\User>
 */
class UserViewController extends ModelViewController
{
	protected Users $siblings;

	public function __construct(
		User $model
	) {
		parent::__construct($model);
	}

	public function breadcrumb(): array
	{
		return [
			[
				'label' => $this->model->username(),
				'link'  => $this->panel->url(true),
			]
		];
	}

	public function buttons(): ViewButtons
	{
		return parent::buttons()->defaults(
			'theme',
			'settings',
			'languages'
		);
	}

	public static function factory(string|null $id = null): static
	{
		return new static(model: Find::user($id));
	}

	public function next(): array|null
	{
		return static::prevNext(
			$this->model->next($this->siblings()),
			'username'
		);
	}

	public function prev(): array|null
	{
		return static::prevNext(
			$this->model->prev($this->siblings()),
			'username'
		);
	}

	public function props(): array
	{
		$permissions = $this->model->panel()->options();

		return [
			...parent::props(),
			'avatar'            => $this->model->avatar()?->url(),
			'blueprint'         => $this->model->role()->name(),
			'canChangeEmail'    => $permissions['changeEmail'],
			'canChangeLanguage' => $permissions['changeLanguage'],
			'canChangeName'     => $permissions['changeName'],
			'canChangeRole'     => $this->model->roles()->count() > 1,
			'email'             => $this->model->email(),
			'language'          => $this->kirby->translation($this->model->language())->name(),
			'name'              => $this->model->name()->toString(),
			'role'              => $this->model->role()->title(),
			'search'            => 'users',
			'username'          => $this->model->username(),
		];
	}

	protected function siblings(): Users
	{
		return $this->siblings ??= $this->model
			->siblings()
			->filter('isListable', true);
	}

	public function title(): string
	{
		return $this->model->username() ?: $this->model->id();
	}
}
