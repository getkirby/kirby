<?php

use Kirby\Cms\ModelWithContent;
use Kirby\Data\Data;
use Kirby\Toolkit\A;

return [
	'mixins' => [
		'filepicker',
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
		 * Sets the file(s), which are selected by default when a new page is created
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
		'query' => function () {
			return $this->query ?? $this->parentModel::CLASS_ALIAS . '.files';
		},
		'default' => function () {
			return $this->toFiles($this->default);
		},
		'value' => function () {
			return $this->toFiles($this->value);
		},
	],
	'methods' => [
		'fileResponse' => function ($file) {
			return $file->panel()->pickerData([
				'image'  => $this->image,
				'info'   => $this->info ?? false,
				'layout' => $this->layout,
				'model'  => $this->model(),
				'text'   => $this->text,
			]);
		},
		'toFiles' => function ($value = null) {
			$files = [];

			foreach (Data::decode($value, 'yaml') as $id) {
				if (is_array($id) === true) {
					$id = $id['uuid'] ?? $id['id'] ?? null;
				}

				if (
					$id !== null &&
					($file = $this->kirby()->file($id, $this->model()))
				) {
					$files[] = $this->fileResponse($file);
				}
			}

			return $files;
		}
	],
	'api' => function () {
		return [
			[
				'pattern' => '/',
				'action'  => function () {
					$field = $this->field();

					return $field->filepicker([
						'image'  => $field->image(),
						'info'   => $field->info(),
						'layout' => $field->layout(),
						'limit'  => $field->limit(),
						'page'   => $this->requestQuery('page'),
						'query'  => $field->query(),
						'search' => $this->requestQuery('search'),
						'text'   => $field->text()
					]);
				}
			],
			[
				'pattern' => 'upload',
				'method'  => 'POST',
				'action'  => function () {
					$field   = $this->field();
					$uploads = $field->uploads();

					// move_uploaded_file() not working with unit test
					// @codeCoverageIgnoreStart
					return $field->upload($this, $uploads, function ($file, $parent) use ($field) {
						return $file->panel()->pickerData([
							'image'  => $field->image(),
							'info'   => $field->info(),
							'layout' => $field->layout(),
							'model'  => $field->model(),
							'text'   => $field->text(),
						]);
					});
					// @codeCoverageIgnoreEnd
				}
			]
		];
	},
	'save' => function ($value = null) {
		return A::pluck($value, $this->store);
	},
	'validations' => [
		'max',
		'min'
	]
];
