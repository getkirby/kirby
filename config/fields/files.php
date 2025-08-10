<?php

use Kirby\Cms\File;
use Kirby\Cms\ModelWithContent;
use Kirby\Panel\Controller\Dialog\FilesPickerDialogController;

return [
	'mixins' => [
		'layout',
		'min',
		'picker',
		'upload'
	],
	'props' => [
		/**
		 * Unset inherited props
		 */
		'after'       => null,
		'before'      => null,
		'autofocus'   => null,
		'icon'        => null,
		'placeholder' => null,

		/**
		 * Sets the file(s), which are selected by default
		 * when a new page is created
		 */
		'default' => function ($default = null) {
			return $default;
		},

		'value' => function ($value = null) {
			return $value;
		}
	],
	'computed' => [
		'parentModel' => function () {
			if (
				is_string($this->parent) === true &&
				$model = $this->model()->query(
					$this->parent,
					ModelWithContent::class
				)
			) {
				return $model;
			}

			return $this->model();
		},
		'parent' => function () {
			return $this->parentModel->apiUrl(true);
		},
		'default' => function () {
			return $this->toFormValues($this->default);
		},
		'value' => function () {
			return $this->toFormValues($this->value);
		},
	],
	'methods' => [
		'toId' => function (File $file) {
			return match ($file->parent() !== $this->model()) {
				true  => $file->id(),
				false => $file->filename()
			};
		},
		'toModel' => function (string $id) {
			return $this->kirby()->file($id, $this->model);
		}
	],
	'api' => function () {
		return [
			[
				'pattern' => 'items',
				'method'  => 'GET',
				'action'  => fn () => $this->field()->itemsFromRequest()
			],
			[
				'pattern' => 'upload',
				'method'  => 'POST',
				'action'  => function () {
					$field = $this->field();

					// move_uploaded_file() not working with unit test
					// @codeCoverageIgnoreStart
					return $field->upload(
						$this,
						$field->uploads(),
						fn ($file, $parent) => $field->toItem($file)
					);
					// @codeCoverageIgnoreEnd
				}
			]
		];
	},
	'dialogs' => fn () =>  [
		'picker' => fn () => new FilesPickerDialogController(...[
			'model'     => $this->model(),
			'hasSearch' => $this->search,
			'image'     => $this->image,
			'info'      => $this->info ?? false,
			'limit'     => $this->limit,
			'max'       => $this->max,
			'multiple'  => $this->multiple,
			'query'     => $this->query,
			'text'      => $this->text,
			...$this->picker
		])
	],
	'save' => function ($value = null) {
		return $this->toStoredValues($value);
	},
	'validations' => [
		'max',
		'min'
	]
];
