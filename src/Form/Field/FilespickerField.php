<?php

namespace Kirby\Form\Field;

use Kirby\Cms\File;
use Kirby\Cms\FilePicker;
use Kirby\Cms\ModelWithContent;
use Kirby\Form\Mixin;
use Kirby\Panel\Ui\Item\FileItem;

/**
 * Filespicker field
 *
 * @package   Kirby Field
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
class FilespickerField extends ModelspickerField
{
	use Mixin\Upload;

	protected string|null $parent;

	public function __construct(
		bool|null $autofocus = null,
		mixed $default = null,
		bool|null $disabled = null,
		array|string|null $empty = null,
		array|string|null $help = null,
		array|null $image = null,
		string|null $info = null,
		array|string|null $label = null,
		string|null $layout = null,
		bool|null $link = null,
		int|null $max = null,
		int|null $min = null,
		bool|null $multiple = null,
		string|null $name = null,
		string|null $parent = null,
		string|null $query = null,
		bool|null $required = null,
		bool|null $search = null,
		string|null $size = null,
		string|null $store = null,
		string|null $text = null,
		bool|null $translate = null,
		mixed $uploads = null,
		array|null $when = null,
		string|null $width = null
	) {
		parent::__construct(
			autofocus: $autofocus,
			default: $default,
			disabled: $disabled,
			empty: $empty,
			help: $help,
			image: $image,
			info: $info,
			label: $label,
			layout: $layout,
			link: $link,
			max: $max,
			min: $min,
			multiple: $multiple,
			name: $name,
			query: $query,
			required: $required,
			search: $search,
			size: $size,
			store: $store,
			text: $text,
			translate: $translate,
			when: $when,
			width: $width
		);

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
					/**
					 * @var \Kirby\Api\Api
					 */
					$api = $this;

					// move_uploaded_file() not working with unit test
					// @codeCoverageIgnoreStart
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

	public function picker(): FilePicker
	{
		return new FilePicker([
			'image'  => $this->image(),
			'info'   => $this->info(),
			'layout' => $this->layout(),
			'model'  => $this->model(),
			'page'   => $this->kirby()->api()->requestQuery('page'),
			'query'  => $this->query(),
			'search' => $this->kirby()->api()->requestQuery('search'),
			'text'   => $this->text()
		]);
	}

	public function props(): array
	{
		return [
			...parent::props(),
			'parent'  => $this->parent(),
			'uploads' => $this->uploads()
		];
	}

	public function query(): string
	{
		return $this->query ?? $this->parentModel()::CLASS_ALIAS . '.files';
	}

	/**
	 * @param \Kirby\Cms\File $model
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
	 * @param \Kirby\Cms\File $model
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
