<?php

use Kirby\Cms\Blueprint;
use Kirby\Cms\Page;
use Kirby\Cms\Pages;
use Kirby\Cms\Site;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Panel\Collector\PagesCollector;
use Kirby\Panel\Ui\Item\PageItem;
use Kirby\Toolkit\A;
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
		 * Optional array of templates that should only be allowed to add
		 * or `false` to completely disable page creation
		 */
		'create' => function ($create = null) {
			return $create;
		},
		/**
		 * Filters pages by a query. Sorting will be disabled
		 */
		'query' => function (string|null $query = null) {
			return $query;
		},
		/**
		 * Filters pages by their status. Available status settings: `draft`, `unlisted`, `listed`, `published`, `all`.
		 */
		'status' => function (string $status = '') {
			if ($status === 'drafts') {
				$status = 'draft';
			}

			if (in_array($status, ['all', 'draft', 'published', 'listed', 'unlisted'], true) === false) {
				$status = 'all';
			}

			return $status;
		},
		/**
		 * Filters the list by single template.
		 */
		'template' => function (string|array|null $template = null) {
			return $template;
		},
		/**
		 * Filters the list by templates and sets template options when adding new pages to the section.
		 */
		'templates' => function ($templates = null) {
			return A::wrap($templates ?? $this->template);
		},
		/**
		 * Excludes the selected templates.
		 */
		'templatesIgnore' => function ($templates = null) {
			return A::wrap($templates);
		}
	],
	'computed' => [
		'parent' => function () {
			$parent = $this->parentModel();

			if (
				$parent instanceof Site === false &&
				$parent instanceof Page === false
			) {
				throw new InvalidArgumentException(
					message: 'The parent is invalid. You must choose the site or a page as parent.'
				);
			}

			return $parent;
		},
		'collector' => function () {
			return $this->collector ??= new PagesCollector(
				limit: $this->limit(),
				page: $this->page() ?? 1,
				parent: $this->parent(),
				query: $this->query(),
				status: $this->status(),
				templates: $this->templates(),
				templatesIgnore: $this->templatesIgnore(),
				search: $this->searchterm(),
				sortBy: $this->sortBy(),
				flip: $this->flip()
			);
		},
		'models' => function () {
			return $this->collector()->models();
		},
		'modelsPaginated' => function () {
			return $this->collector()->models(paginated: true);
		},
		'pages' => function () {
			return $this->models();
		},
		'total' => function () {
			return $this->models()->count();
		},
		'data' => function () {
			$data = [];

			foreach ($this->modelsPaginated() as $page) {
				$item = (new PageItem(
					page: $page,
					image: $this->image,
					layout: $this->layout,
					info: $this->info,
					text: $this->text,
				))->props();

				if ($this->layout === 'table') {
					$item = $this->columnsValues($item, $page);
				}

				$data[] = $item;
			}

			return $data;
		},
		'errors' => function () {
			$errors = [];

			if ($this->validateMax() === false) {
				$errors['max'] = I18n::template('error.section.pages.max.' . I18n::form($this->max), [
					'max'     => $this->max,
					'section' => $this->headline
				]);
			}

			if ($this->validateMin() === false) {
				$errors['min'] = I18n::template('error.section.pages.min.' . I18n::form($this->min), [
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
		'add' => function () {
			if ($this->create === false) {
				return false;
			}

			if ($this->isFull() === true) {
				return false;
			}

			// form here on, we need to check with which status
			// the pages are created and if the section can show
			// these newly created pages

			// if the section shows pages no matter what status they have,
			// we can always show the add button
			if ($this->status === 'all') {
				return true;
			}

			// collect all statuses of the blueprints
			// that are allowed to be created
			$statuses = [];

			foreach ($this->blueprintNames() as $blueprint) {
				try {
					$props      = Blueprint::load('pages/' . $blueprint);
					$statuses[] = $props['create']['status'] ?? 'draft';
				} catch (Throwable) {
					$statuses[] = 'draft'; // @codeCoverageIgnore
				}
			}

			$statuses = array_unique($statuses);

			// if there are multiple statuses or if the section is showing
			// a different status than new pages would be created with,
			// we cannot show the add button
			if (count($statuses) > 1 || $this->status !== $statuses[0]) {
				return false;
			}

			return true;
		},
		'pagination' => function () {
			return $this->pagination();
		}
	],
	'methods' => [
		'blueprints' => function () {
			$blueprints = [];

			// convert every template to a usable option array
			// for the template select box
			foreach ($this->blueprintNames() as $blueprint) {
				try {
					$props = Blueprint::load('pages/' . $blueprint);

					$blueprints[] = [
						'name'  => basename($props['name']),
						'title' => $props['title'],
					];
				} catch (Throwable) {
					$blueprints[] = [
						'name'  => basename($blueprint),
						'title' => ucfirst($blueprint),
					];
				}
			}

			return $blueprints;
		},
		'blueprintNames' => function () {
			$blueprints  = empty($this->create) === false ? A::wrap($this->create) : $this->templates;

			if (empty($blueprints) === true) {
				$blueprints = $this->kirby()->blueprints();
			}

			// excludes ignored templates
			if ($templatesIgnore = $this->templatesIgnore) {
				$blueprints = array_diff($blueprints, $templatesIgnore);
			}

			return $blueprints;
		},
	],
	// @codeCoverageIgnoreStart
	'api' => function () {
		return [
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
				'add'      => $this->add,
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
				'sortable' => $this->sortable
			],
			'pagination' => $this->pagination,
		];
	}
];
