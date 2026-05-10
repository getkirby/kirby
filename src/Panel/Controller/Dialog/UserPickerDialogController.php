<?php

namespace Kirby\Panel\Controller\Dialog;

use Kirby\Cms\ModelWithContent;
use Kirby\Cms\User;
use Kirby\Panel\Collector\UsersCollector;
use Kirby\Panel\Ui\Item\UserItem;

/**
 * Controls the Panel dialog for selecting users
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 *
 * @unstable
 */
class UserPickerDialogController extends ModelPickerDialogController
{
	protected UsersCollector $collector;

	public function __construct(
		ModelWithContent $model,
		bool $hasSearch = true,
		array|null $image = [],
		string|null $info = null,
		string $layout = 'list',
		public int|null $limit = null,
		int|null $max = null,
		bool $multiple = true,
		public string|null $query = null,
		string|null $size = null,
		string|null $text = null
	) {
		parent::__construct(
			model:     $model,
			hasSearch: $hasSearch,
			image:     $image,
			info:      $info,
			layout:    $layout,
			max:       $max,
			multiple:  $multiple,
			size:      $size,
			text:      $text
		);
	}

	public function collector(): UsersCollector
	{
		return $this->collector ??= new UsersCollector(
			limit:  $this->limit,
			page:   $this->page,
			parent: $this->model,
			query:  $this->query(),
			search: $this->search,
			sortBy: 'username asc'
		);
	}

	protected function empty(): array
	{
		return [
			'icon' => 'users',
			'text' => $this->i18n('dialog.users.empty')
		];
	}

	public function find(string $id): User|null
	{
		return $this->kirby->user($id);
	}

	/**
	 * Returns the item data for a user
	 * @param \Kirby\Cms\User $model
	 */
	public function item(ModelWithContent $model): array
	{
		return (new UserItem(
			user: $model,
			image: $this->image,
			info: $this->info,
			layout: $this->layout,
			text: $this->text
		))->props();
	}

	public function query(): string
	{
		if ($this->query !== null) {
			return $this->query;
		}

		if ($this->model instanceof User) {
			return 'user.siblings';
		}

		return 'kirby.users';
	}
}
