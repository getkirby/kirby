<?php

namespace Kirby\Panel\Ui;

use Kirby\Cms\Files;
use Kirby\Cms\ModelWithContent;

/**
 * @package   Kirby Panel
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     5.1.0
 */
class FilesCollection extends ModelsCollection
{
	public function __construct(
		public Files $files,
		public array $columns = [],
		public string $component = 'k-collection',
		public array|null $empty = null,
		public string|null $help = null,
		public array|null|bool $image = null,
		public string|null $info = null,
		public string $layout = 'list',
		public array|bool $pagination = false,
		public bool $selecting = false,
		public bool $sortable = false,
		public string $size = 'medium',
		public string|null $text = '{{ file.filename }}',
		public string|null $theme = null,
	) {
		$this->models = $files;
	}

	/**
	 * @param \Kirby\Cms\File $model
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
			'dragText'    => $panel->dragText(),
			'extension'   => $model->extension(),
			'filename'    => $model->filename(),
			'id'          => $model->id(),
			'image'       => $panel->image($image, $layout),
			'info'        => $model->toSafeString($info ?? false),
			'link'        => $panel->url(true),
			'mime'        => $model->mime(),
			'parent'      => $model->parent()->panel()->path(),
			'permissions' => [
				'delete' => $permissions->can('delete'),
				'sort'   => $permissions->can('sort'),
			],
			'template'   => $model->template(),
			'text'       => $model->toSafeString($text),
			'url'        => $model->url(),
		];
	}
}
