<?php

namespace Kirby\Panel\Controller\View;

use Kirby\Cms\Find;
use Kirby\Cms\ModelWithContent;
use Kirby\Cms\User;
use Kirby\Panel\Model;
use Kirby\Panel\Ui\Button\ViewButtons;
use Override;

/**
 * Controls the user view
 *
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
class UserViewController extends ModelViewController
{
	/**
	 * @var \Kirby\Cms\User $model
	 */
	protected ModelWithContent $model;

	/**
	 * @var \Kirby\Panel\User
	 */
	protected Model $panel;

	public function __construct(
		User $model
	) {
		parent::__construct($model);
	}

	#[Override]
	public function breadcrumb(): array
	{
		return [
			[
				'label' => $this->model->username(),
				'link'  => $this->panel->url(true),
			]
		];
	}

	#[Override]
	public function buttons(): ViewButtons
	{
		return parent::buttons()->defaults(
			'theme',
			'settings',
			'languages'
		);
	}

	public static function factory(string $id): static
	{
		return new static(model: Find::user($id));
	}

	#[Override]
	public function next(): array|null
	{
		return static::prevNext($this->model->next(), 'username');
	}

	#[Override]
	public function prev(): array|null
	{
		return static::prevNext($this->model->prev(), 'username');
	}

	#[Override]
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

	#[Override]
	public function title(): string
	{
		return $this->model->username();
	}
}
