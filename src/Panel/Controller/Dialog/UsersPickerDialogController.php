<?php

namespace Kirby\Panel\Controller\Dialog;

use Kirby\Cms\ModelWithContent;
use Kirby\Cms\User;
use Kirby\Panel\Collector\UsersCollector;

/**
 * Controls the Panel dialog for selecting users
 *
 * @package   Kirby Panel
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 * @unstable
 */
class UsersPickerDialogController extends ModelsPickerDialogController
{
	protected const TYPE = 'users';

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

	public function find(string $id): User|null
	{
		return $this->kirby->user($id);
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
