<?php

namespace Kirby\Panel\Ui;

use Kirby\Cms\ModelWithContent;
use Kirby\Cms\Users;

/**
 * @package   Kirby Panel
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     5.1.0
 */
class UsersCollection extends ModelsCollection
{
	public function __construct(
		public Users $users,
		public array $columns = [],
		public string $component = 'k-collection',
		public array|string|null $empty = null,
		public string|null $help = null,
		public array|string|bool|null $image = null,
		public string|null $info = '{{ user.role.title }}',
		public string $layout = 'list',
		public bool $link = true,
		public array|bool $pagination = false,
		public bool $rawValues = false,
		public bool $selecting = false,
		public bool $sortable = false,
		public string $size = 'auto',
		public string|null $text = '{{ user.username }}',
		public string|null $theme = null,
	) {
		$this->models = $users;
	}

	/**
	 * @param \Kirby\Cms\User $model
	 */
	public function item(
		ModelWithContent $model,
		array|string|bool|null $image,
		string|null $info,
		string $layout,
		string $text,
	): array {
		$panel = $model->panel();

		return [
			'id'    => $model->id(),
			'image' => $panel->image($image, $layout),
			'info'  => $model->toSafeString($info ?? false),
			'link'  => $panel->url(true),
			'text'  => $model->toSafeString($text),
		];
	}
}
