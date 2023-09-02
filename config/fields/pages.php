<?php

use Kirby\Cms\App;
use Kirby\Cms\Page;
use Kirby\Data\Data;
use Kirby\Panel\PagesPicker;
use Kirby\Toolkit\A;

return [
	'mixins' => [
		'layout',
		'min',
		'picker',
	],
	'props' => [
		/**
		 * Unset inherited props
		 */
		'after'       => null,
		'autofocus'   => null,
		'before'      => null,
		'icon'        => null,
		'placeholder' => null,

		/**
		 * Default selected page(s) when a new page/file/user is created
		 */
		'default' => function ($default = null) {
			return $this->toPages($default);
		},

		/**
		 * Optional query to select a specific set of pages
		 */
		'query' => function (string $query = null) {
			return $query;
		},

		/**
		 * Optionally include subpages of pages
		 */
		'subpages' => function (bool $subpages = true) {
			return $subpages;
		},

		'value' => function ($value = null) {
			return $this->toPages($value);
		},
	],
	'computed' => [
		/**
		 * Unset inherited computed
		 */
		'default' => null
	],
	'methods' => [
		'toPage' => function (Page $page): array {
			return $page->panel()->pickerData([
				'image'  => $this->image,
				'info'   => $this->info,
				'layout' => $this->layout,
				'text'   => $this->text,
			]);
		},
		'toPages' => function ($value = null): array {
			$pages = [];
			$kirby = App::instance();

			foreach (Data::decode($value, 'yaml') as $id) {
				if (is_array($id) === true) {
					$id =  $id['uuid'] ?? $id['id'] ?? null;
				}

				if ($id !== null && ($page = $kirby->page($id))) {
					$pages[] = $this->toPage($page);
				}
			}

			return $pages;
		}
	],
	'api' => function (): array {
		return [
			[
				'pattern' => '/',
				'action' => function (): array {
					$field  = $this->field();
					$picker = new PagesPicker([
						'image'    => $field->image(),
						'info'     => $field->info(),
						'layout'   => $field->layout(),
						'limit'    => $field->limit(),
						'model'    => $field->model(),
						'page'     => $this->requestQuery('page'),
						'parent'   => $this->requestQuery('parent'),
						'query'    => $field->query(),
						'search'   => $this->requestQuery('search'),
						'subpages' => $field->subpages(),
						'text'     => $field->text()
					]);

					return $picker->toArray();
				}
			]
		];
	},
	'save' => function ($value = null): array {
		return A::pluck($value, $this->store);
	},
	'validations' => [
		'max',
		'min'
	]
];
