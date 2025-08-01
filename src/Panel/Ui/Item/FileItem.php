<?php

namespace Kirby\Panel\Ui\Item;

use Kirby\Cms\File;
use Kirby\Cms\ModelWithContent;
use Kirby\Panel\Model;

/**
 * @package   Kirby Panel
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     5.1.0
 */
class FileItem extends ModelItem
{
	/**
	 * @var \Kirby\Cms\File
	 */
	protected ModelWithContent $model;

	/**
	 * @var \Kirby\Panel\File
	 */
	protected Model $panel;

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
