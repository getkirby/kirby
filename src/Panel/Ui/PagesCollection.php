<?php

namespace Kirby\Panel\Ui;

use Kirby\Cms\ModelWithContent;
use Kirby\Cms\Pages;

/**
 * @package   Kirby Panel
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     5.1.0
 */
class PagesCollection extends ModelsCollection
{
	public function __construct(
		public Pages $pages,
		public array $columns = [],
		public string $component = 'k-collection',
		public array|null $empty = null,
		public string|null $help = null,
		public array|null|bool $image = null,
		public string|null $info = null,
		public string $layout = 'list',
		public bool $link = true,
		public array|bool $pagination = false,
		public bool $selecting = false,
		public bool $sortable = false,
		public string $size = 'medium',
		public string|null $text = '{{ model.title }}',
		public string|null $theme = null,
	) {
		$this->models = $pages;
	}

	/**
	 * @param \Kirby\Cms\Page $model
	 */
	public function item(
		ModelWithContent $model,
		array|null|bool $image,
		string|null $info,
		string $layout,
		string $text,
	): array
	{
		$panel       = $model->panel();
		$permissions = $model->permissions();

		return [
			'dragText' => $panel->dragText(),
			'id'       => $model->id(),
			'image'    => $panel->image($image, $layout === 'table' ? 'list' : $layout),
			'info'     => $model->toSafeString($info ?? false),
			'link'     => $panel->url(true),
			'parent'   => $model->parentId(),
			'permissions' => [
				'delete'       => $permissions->can('delete'),
				'changeSlug'   => $permissions->can('changeSlug'),
				'changeStatus' => $permissions->can('changeStatus'),
				'changeTitle'  => $permissions->can('changeTitle'),
				'sort'         => $permissions->can('sort'),
			],
			'status'     => $model->status(),
			'template'   => $model->intendedTemplate()->name(),
			'text'       => $model->toSafeString($text),
		];
	}
}
