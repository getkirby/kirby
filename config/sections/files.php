<?php

use Kirby\Cms\File;
use Kirby\Cms\Files;
use Kirby\Panel\Collector\FilesCollector;
use Kirby\Panel\Ui\FilesCollection;
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
		'collector' => function (): FilesCollector {
			return $this->collector ??= new FilesCollector(
				flip: $this->flip(),
				limit: $this->limit(),
				page: $this->page(),
				parent: $this->parent(),
				query: $this->query(),
				search: $this->searchterm(),
				sortBy: $this->sortBy(),
				template: $this->templates(),
			);
		},
		'models' => function (): Files {
			return $this->collector()->all();
		},
		'modelsPaginated' => function (): Files {
			return $this->collector()->paginated();
		},
		'component' => function (): FilesCollection {
			return $this->component ??= new FilesCollection(
				files: $this->modelsPaginated(),
				columns: $this->columns(),
				empty: $this->empty(),
				help: $this->help(),
				image: $this->image(),
				info: $this->info(),
				layout: $this->layout(),
				rawValues: $this->rawvalues(),
				sortable: $this->sortable(),
				size: $this->size(),
				text: $this->text(),
				theme: $this->theme(),
			);
		},
		'files' => function (): Files {
			return $this->models();
		},
		'data' => function (): array {
			return $this->component()->items();
		},
		'total' => function (): int {
			return $this->models()->count();
		},
		'errors' => function (): array {
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
			return $this->component()->pagination();
		},
		'upload' => function () {
			if ($this->isFull() === true) {
				return false;
			}

			// count all uploaded files
			$max      = $this->max ? $this->max - $this->total : null;
			$multiple = !$max || $max > 1;
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
		$props      = $this->component()->props();
		$items      = $props['items'];
		$pagination = $props['pagination'];

		unset($props['items'], $props['pagination']);

		return [
			'data'    => $items,
			'errors'  => $this->errors,
			'options' => [
				...$props,
				'accept'   => $this->accept,
				'apiUrl'   => $this->parent->apiUrl(true) . '/sections/' . $this->name,
				'batch'    => $this->batch,
				'headline' => $this->headline,
				'link'     => $this->link(),
				'max'      => $this->max,
				'min'      => $this->min,
				'search'   => $this->search,
				'upload'   => $this->upload
			],
			'pagination' => $pagination
		];
	}
];
