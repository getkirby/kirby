<?php

use Kirby\Cms\File;
use Kirby\Panel\Collector\FilesCollector;
use Kirby\Panel\Ui\Item\FileItem;
use Kirby\Panel\Ui\Upload;
use Kirby\Toolkit\I18n;

return [
	'mixins' => [
		'batch',
		'details',
		'empty',
		'headline',
		'help',
		'layout',
		'min',
		'max',
		'pagination',
		'parent',
		'search',
		'sort'
	],
	'props' => [
		/**
		 * Option to switch off the upload button
		 */
		'create' => function (bool $create = true) {
			return $create;
		},
		/**
		 * Filters pages by a query. Sorting will be disabled
		 */
		'query' => function (string|null $query = null) {
			return $query;
		},
		/**
		 * Filters all files by template and also sets the template, which will be used for all uploads
		 */
		'template' => function (string|null $template = null) {
			return $template;
		},
		/**
		 * Setup for the main text in the list or cards. By default this will display the filename.
		 */
		'text' => function ($text = '{{ file.filename }}') {
			return I18n::translate($text, $text);
		},
	],
	'computed' => [
		'accept' => function () {
			if ($this->template) {
				$file = new File([
					'filename' => 'tmp',
					'parent'   => $this->model(),
					'template' => $this->template
				]);

				return $file->blueprint()->acceptAttribute();
			}

			return null;
		},
		'parent' => function () {
			return $this->parentModel();
		},
		'collector' => function () {
			return $this->collector ??= new FilesCollector(
				flip: $this->flip(),
				limit: $this->limit(),
				page: $this->page() ?? 1,
				parent: $this->parent(),
				query: $this->query(),
				search: $this->searchterm(),
				sortBy: $this->sortBy(),
				template: $this->template(),
			);
		},
		'models' => function () {
			return $this->collector()->models();
		},
		'modelsPaginated' => function () {
			return $this->collector()->models(paginated: true);
		},
		'files' => function () {
			return $this->models;
		},
		'data' => function () {
			$data               = [];
			$dragTextIsAbsolute = $this->model->is($this->parent) === false;

			foreach ($this->modelsPaginated() as $file) {
				$item = (new FileItem(
					file: $file,
					dragTextIsAbsolute: $dragTextIsAbsolute,
					image: $this->image,
					layout: $this->layout,
					info: $this->info,
					text: $this->text,
				))->props();

				if ($this->layout === 'table') {
					$item = $this->columnsValues($item, $file);
				}

				$data[] = $item;
			}

			return $data;
		},
		'total' => function () {
			return $this->models()->count();
		},
		'errors' => function () {
			$errors = [];

			if ($this->validateMax() === false) {
				$errors['max'] = I18n::template('error.section.files.max.' . I18n::form($this->max), [
					'max'     => $this->max,
					'section' => $this->headline
				]);
			}

			if ($this->validateMin() === false) {
				$errors['min'] = I18n::template('error.section.files.min.' . I18n::form($this->min), [
					'min'     => $this->min,
					'section' => $this->headline
				]);
			}

			if (empty($errors) === true) {
				return [];
			}

			return [
				$this->name => [
					'label'   => $this->headline,
					'message' => $errors,
				]
			];
		},
		'pagination' => function () {
			return $this->pagination();
		},
		'upload' => function () {
			if ($this->create === false) {
				return false;
			}

			if ($this->isFull() === true) {
				return false;
			}

			$settings = new Upload(
				api: $this->parent->apiUrl(true) . '/files',
				accept: $this->accept,
				max: $this->max ? $this->max - $this->total : null,
				preview: $this->image,
				sort: $this->sortable === true ? $this->total + 1 : null,
				template: $this->template,
			);

			return $settings->props();
		}
	],
	// @codeCoverageIgnoreStart
	'api' => function () {
		return [
			[
				'pattern' => 'sort',
				'method'  => 'PATCH',
				'action'  => function () {
					$this->section()->model()->files()->changeSort(
						$this->requestBody('files'),
						$this->requestBody('index')
					);

					return true;
				}
			],
			[
				'pattern' => 'delete',
				'method'  => 'DELETE',
				'action'  => function () {
					return $this->section()->deleteSelected(
						ids: $this->requestBody('ids'),
					);
				}
			]
		];
	},
	// @codeCoverageIgnoreEnd
	'toArray' => function () {
		return [
			'data'    => $this->data,
			'errors'  => $this->errors,
			'options' => [
				'accept'   => $this->accept,
				'apiUrl'   => $this->parent->apiUrl(true) . '/sections/' . $this->name,
				'batch'    => $this->batch,
				'columns'  => $this->columnsWithTypes(),
				'empty'    => $this->empty,
				'headline' => $this->headline,
				'help'     => $this->help,
				'layout'   => $this->layout,
				'link'     => $this->link(),
				'max'      => $this->max,
				'min'      => $this->min,
				'search'   => $this->search,
				'size'     => $this->size,
				'sortable' => $this->sortable,
				'upload'   => $this->upload
			],
			'pagination' => $this->pagination
		];
	}
];
