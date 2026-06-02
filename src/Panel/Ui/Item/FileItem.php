<?php

namespace Kirby\Panel\Ui\Item;

use Kirby\Cms\File;

/**
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     5.1.0
 *
 * @extends \Kirby\Panel\Ui\Item\ModelItem<\Kirby\Cms\File, \Kirby\Panel\File>
 */
class FileItem extends ModelItem
{
	public function __construct(
		File $file,
		protected bool $dragTextIsAbsolute = false,
		string|array|false|null $image = [],
		string|null $info = null,
		string|null $layout = null,
		string|null $text = null,
	) {
		parent::__construct(
			model: $file,
			image: $image,
			info: $info,
			layout: $layout,
			text: $text ?? '{{ file.filename }}',
		);
	}

	protected function dragText(): string
	{
		return $this->panel->dragText(absolute: $this->dragTextIsAbsolute);
	}

	protected function permissions(): array
	{
		$permissions = $this->model->permissions();

		return [
			'delete' => $permissions->can('delete'),
			'sort'   => $permissions->can('sort'),
		];
	}

	public function props(): array
	{
		return [
			...parent::props(),
			'dragText'  => $this->dragText(),
			'extension' => $this->model->extension(),
			'filename'  => $this->model->filename(),
			'mime'      => $this->model->mime(),
			'parent'    => $this->model->parent()->panel()->path(),
			'template'  => $this->model->template(),
			'url'       => $this->model->url(),
		];
	}
}
