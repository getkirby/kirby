<?php

namespace Kirby\Form\Field;

use Kirby\Api\Api;
use Kirby\Cms\File;
use Kirby\Cms\ModelWithContent;
use Kirby\Form\Mixin;
use Kirby\Panel\Controller\Dialog\FilePickerDialogController;
use Kirby\Panel\Ui\Item\FileItem;

/**
 * Filepicker field
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 *
 * @extends ModelPickerField<File>
 */
class FilePickerField extends ModelPickerField
{
	use Mixin\Upload;

	protected string|null $parent;

	public function __construct(
		string|null $parent = null,
		mixed $uploads = null,
		mixed ...$args
	) {
		parent::__construct(...$args);

		$this->parent  = $parent;
		$this->uploads = $uploads;
	}

	public function api(): array
	{
		$field = $this;

		return [
			...parent::api(),
			[
				'pattern' => 'upload',
				'method'  => 'POST',
				'action'  => function () use ($field) {
					// @codeCoverageIgnoreStart
					/**
					 * @var Api
					 */
					$api = $this;

					// move_uploaded_file() not working with unit test
					return $field->upload(
						$api,
						$field->uploads(),
						fn ($file, $parent) => $field->toItem($file)
					);
					// @codeCoverageIgnoreEnd
				}
			]
		];
	}

	public function dialogs(): array
	{
		return [
			'picker' => fn () => new FilePickerDialogController(...[
				'model'     => $this->model(),
				'hasSearch' => $this->search(),
				'image'     => $this->image(),
				'info'      => $this->info(),
				'max'       => $this->max(),
				'multiple'  => $this->multiple(),
				'query'     => $this->query(),
				'text'      => $this->text(),
				...$this->picker()
			])
		];
	}

	public function parentModel(): ModelWithContent
	{
		$parent = $this->parent;

		if (is_string($parent) === true) {
			$model = $this->model()->query($parent, ModelWithContent::class);
		}

		return $model ?? $this->model();
	}

	public function parent(): string
	{
		return $this->parentModel()->apiUrl(true);
	}

	public function props(): array
	{
		return [
			...parent::props(),
			'parent'  => $this->parent(),
			'uploads' => $this->uploads()
		];
	}

	public function query(): string|null
	{
		return $this->query;
	}

	/**
	 * @param File $model
	 */
	public function store(ModelWithContent|null $model = null): string
	{
		// store only the filename if the file belongs to the current model
		if ($model?->parent()->is($this->model()) === true) {
			return 'filename';
		}

		return parent::store($model);
	}

	/**
	 * @param File $model
	 */
	public function toItem(ModelWithContent $model): array
	{
		return (new FileItem(
			file:               $model,
			dragTextIsAbsolute: $model->parent()->is($this->model()) === false,
			image:              $this->image(),
			info:               $this->info(),
			layout:             $this->layout(),
			text:               $this->text()
		))->props();
	}

	public function toModel(string $id): File|null
	{
		return $this->kirby()->file($id, $this->model());
	}
}
