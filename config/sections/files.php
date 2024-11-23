<?php

use Kirby\Cms\File;
use Kirby\Cms\Files;
use Kirby\Toolkit\I18n;

return [
	'mixins' => [
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
		}
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
		'models' => function () {
			if ($this->query !== null) {
				$files = $this->parent->query($this->query, Files::class) ?? new Files([]);
			} else {
				$files = $this->parent->files();
			}

			// filter files by template
			$files = $files->template($this->template);

			// filter out all protected and hidden files
			$files = $files->filter('isListable', true);

			// search
			if ($this->search === true && empty($this->searchterm()) === false) {
				$files = $files->search($this->searchterm());

				// disable flip and sortBy while searching
				// to show most relevant results
				$this->flip = false;
				$this->sortBy = null;
			}

			// sort
			if ($this->sortBy) {
				$files = $files->sort(...$files::sortArgs($this->sortBy));
			} else {
				$files = $files->sorted();
			}

			// flip
			if ($this->flip === true) {
				$files = $files->flip();
			}

			// apply the default pagination
			$files = $files->paginate([
				'page'   => $this->page,
				'limit'  => $this->limit,
				'method' => 'none' // the page is manually provided
			]);

			return $files;
		},
		'files' => function () {
			return $this->models;
		},
		'data' => function () {
			$data = [];

			// the drag text needs to be absolute when the files come from
			// a different parent model
			$dragTextAbsolute = $this->model->is($this->parent) === false;

			foreach ($this->models as $file) {
				$panel = $file->panel();

				$item = [
					'dragText'  => $panel->dragText('auto', $dragTextAbsolute),
					'extension' => $file->extension(),
					'filename'  => $file->filename(),
					'id'        => $file->id(),
					'image'     => $panel->image(
						$this->image,
						$this->layout === 'table' ? 'list' : $this->layout
					),
					'info'      => $file->toSafeString($this->info ?? false),
					'link'      => $panel->url(true),
					'mime'      => $file->mime(),
					'parent'    => $file->parent()->panel()->path(),
					'template'  => $file->template(),
					'text'      => $file->toSafeString($this->text),
					'url'       => $file->url(),
				];

				if ($this->layout === 'table') {
					$item = $this->columnsValues($item, $file);
				}

				$data[] = $item;
			}

			return $data;
		},
		'total' => function () {
			return $this->models->pagination()->total();
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
			if ($this->isFull() === true) {
				return false;
			}

			// count all uploaded files
			$max = $this->max ? $this->max - $this->total : null;

			if ($this->max && $this->total === $this->max - 1) {
				$multiple = false;
			} else {
				$multiple = true;
			}

			$template = $this->template === 'default' ? null : $this->template;

			return [
				'accept'     => $this->accept,
				'multiple'   => $multiple,
				'max'        => $max,
				'api'        => $this->parent->apiUrl(true) . '/files',
				'preview'    => $this->image,
				'attributes' => [
					// TODO: an edge issue that needs to be solved:
					//		 if multiple users load the same section
					//       at the same time and upload a file,
					//       uploaded files have the same sort number
					'sort'     => $this->sortable === true ? $this->total + 1 : null,
					'template' => $template
				]
			];
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
