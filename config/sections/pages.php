<?php

use Kirby\Cms\Blueprint;
use Kirby\Cms\Page;
use Kirby\Cms\Pages;
use Kirby\Cms\Site;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Toolkit\A;
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

			if (in_array($status, ['all', 'draft', 'published', 'listed', 'unlisted']) === false) {
				$status = 'all';
			}

			return $status;
		},
		/**
		 * Filters the list by single template.
		 */
		'template' => function (string|array $template = null) {
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
				throw new InvalidArgumentException('The parent is invalid. You must choose the site or a page as parent.');
			}

			return $parent;
		},
		'models' => function () {
			if ($this->query !== null) {
				$pages = $this->parent->query($this->query, Pages::class) ?? new Pages([]);
			} else {
				$pages = match ($this->status) {
					'draft'     => $this->parent->drafts(),
					'listed'    => $this->parent->children()->listed(),
					'published' => $this->parent->children(),
					'unlisted'  => $this->parent->children()->unlisted(),
					default     => $this->parent->childrenAndDrafts()
				};
			}

			// filters pages that are protected and not in the templates list
			// internal `filter()` method used instead of foreach loop that previously included `unset()`
			// because `unset()` is updating the original data, `filter()` is just filtering
			// also it has been tested that there is no performance difference
			// even in 0.1 seconds on 100k virtual pages
			$pages = $pages->filter(function ($page) {
				// remove all protected and hidden pages
				if ($page->isListable() === false) {
					return false;
				}

				$intendedTemplate = $page->intendedTemplate()->name();

				// filter by all set templates
				if (
					$this->templates &&
					in_array($intendedTemplate, $this->templates) === false
				) {
					return false;
				}

				// exclude by all ignored templates
				if (
					$this->templatesIgnore &&
					in_array($intendedTemplate, $this->templatesIgnore) === true
				) {
					return false;
				}

				return true;
			});

			// search
			if ($this->search === true && empty($this->searchterm()) === false) {
				$pages = $pages->search($this->searchterm());

				// disable flip and sortBy while searching
				// to show most relevant results
				$this->flip = false;
				$this->sortBy = null;
			}

			// sort
			if ($this->sortBy) {
				$pages = $pages->sort(...$pages::sortArgs($this->sortBy));
			}

			// flip
			if ($this->flip === true) {
				$pages = $pages->flip();
			}

			// pagination
			$pages = $pages->paginate([
				'page'   => $this->page,
				'limit'  => $this->limit,
				'method' => 'none' // the page is manually provided
			]);

			return $pages;
		},
		'pages' => function () {
			return $this->models;
		},
		'total' => function () {
			return $this->models->pagination()->total();
		},
		'data' => function () {
			$data = [];

			foreach ($this->models as $page) {
				$panel       = $page->panel();
				$permissions = $page->permissions();

				$item = [
					'dragText'    => $panel->dragText(),
					'id'          => $page->id(),
					'image'       => $panel->image(
						$this->image,
						$this->layout === 'table' ? 'list' : $this->layout
					),
					'info'        => $page->toSafeString($this->info ?? false),
					'link'        => $panel->url(true),
					'parent'      => $page->parentId(),
					'permissions' => [
						'sort'         => $permissions->can('sort'),
						'changeSlug'   => $permissions->can('changeSlug'),
						'changeStatus' => $permissions->can('changeStatus'),
						'changeTitle'  => $permissions->can('changeTitle'),
					],
					'status'      => $page->status(),
					'template'    => $page->intendedTemplate()->name(),
					'text'        => $page->toSafeString($this->text),
				];

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

			if (in_array($this->status, ['draft', 'all']) === false) {
				return false;
			}

			if ($this->isFull() === true) {
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
			$templates  = empty($this->create) === false ? A::wrap($this->create) : $this->templates;

			if (empty($templates) === true) {
				$templates = $this->kirby()->blueprints();
			}

			// excludes ignored templates
			if ($templatesIgnore = $this->templatesIgnore) {
				$templates = array_diff($templates, $templatesIgnore);
			}

			// convert every template to a usable option array
			// for the template select box
			foreach ($templates as $template) {
				try {
					$props = Blueprint::load('pages/' . $template);

					$blueprints[] = [
						'name'  => basename($props['name']),
						'title' => $props['title'],
					];
				} catch (Throwable) {
					$blueprints[] = [
						'name'  => basename($template),
						'title' => ucfirst($template),
					];
				}
			}

			return $blueprints;
		}
	],
	'toArray' => function () {
		return [
			'data'    => $this->data,
			'errors'  => $this->errors,
			'options' => [
				'add'      => $this->add,
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
