<?php

use Kirby\Cms\App;
use Kirby\Panel\Lab\Category;
use Kirby\Panel\Lab\Doc;
use Kirby\Panel\Lab\Docs;
use Kirby\Panel\Lab\Responses;

return [
	'lab' => [
		'pattern' => 'lab',
		'action'  => function () {
			return [
				'component' => 'k-lab-index-view',
				'props' => [
					'categories' => Category::all(),
					'info'       => Category::isInstalled() ? null : 'The default Lab examples are not installed.',
					'tab'        => 'examples',
				],
			];
		}
	],
	'lab.docs' => [
		'pattern' => 'lab/docs',
		'action'  => function () {
			$view = [
				'component' => 'k-lab-index-view',
				'title'     => 'Docs',
				'breadcrumb' => [
					[
						'label' => 'Docs',
						'link'  => 'lab/docs'
					]
				]
			];

			// if docs are not installed, show info message
			if (Docs::isInstalled() === false) {
				return [
					...$view,
					'props' => [
						'info' => 'The UI docs are not installed.',
						'tab'  => 'docs',
					],
				];
			}

			return [
				...$view,
				'props' => [
					'categories' => [
						['examples' => Docs::all()]
					],
					'tab'        => 'docs',
				],
			];
		}
	],
	'lab.doc' => [
		'pattern' => 'lab/docs/(:any)',
		'action'  => function (string $component) {
			$crumbs = [
				[
					'label' => 'Docs',
					'link'  => 'lab/docs'
				],
				[
					'label' => $component,
					'link'  => 'lab/docs/' . $component
				]
			];

			if (Docs::isInstalled() === false) {
				return [
					'component'  => 'k-lab-index-view',
					'title'      => $component,
					'breadcrumb' => $crumbs,
					'props'      => [
						'info' => 'The UI docs are not installed.',
						'tab'  => 'docs',
					],
				];
			}

			$doc = Doc::factory($component);

			if ($doc === null) {
				return [
					'component'  => 'k-lab-index-view',
					'title'      => $component,
					'breadcrumb' => $crumbs,
					'props'      => [
						'info' => 'No UI docs found for ' . $component . '.',
						'tab'  => 'docs',
					],
				];
			}

			// header buttons
			$buttons = [];

			if ($lab = $doc->lab()) {
				$buttons[] = [
					'props' => [
						'text' => 'Lab examples',
						'icon' => 'lab',
						'link' => '/lab/' . $lab
					]
				];
			}

			$buttons[] = [
				'props' => [
					'icon'   => 'github',
					'link'   => $doc->source(),
					'target' => '_blank'
				]
			];

			return [
				'component'  => 'k-lab-docs-view',
				'title'      => $component,
				'breadcrumb' => $crumbs,
				'props'      => [
					'buttons'   => $buttons,
					'component' => $component,
					'docs'      => $doc->toArray(),
					'lab'       => $lab
				]
			];
		}
	],
	'lab.errors' => [
		'pattern' => 'lab/errors/(:any?)',
		'action'  => fn (string|null $type = null) => Responses::errorResponseByType($type)
	],
	'lab.vue' => [
		'pattern' => [
			'lab/(:any)/(:any)/index.vue',
			'lab/(:any)/(:any)/(:any)/index.vue'
		],
		'action'  => function (
			string $category,
			string $id,
			string|null $tab = null
		) {
			return Category::factory($category)->example($id, $tab)->serve();
		}
	],
	'lab.example' => [
		'pattern' => 'lab/(:any)/(:any)/(:any?)',
		'action'  => function (
			string $category,
			string $id,
			string|null $tab = null
		) {
			$category = Category::factory($category);
			$example  = $category->example($id, $tab);
			$props    = $example->props();
			$vue      = $example->vue();
			$compiler = App::instance()->option('panel.vue.compiler', true);

			if ($doc = $props['docs'] ?? null) {
				$doc = Doc::factory($doc);
			}

			$github = $doc?->source();

			if ($source = $props['source'] ?? null) {
				$github ??= 'https://github.com/getkirby/kirby/tree/main/' . $source;
			}

			// header buttons
			$buttons = [];

			if ($doc) {
				$buttons[] = [
					'props' => [
						'text'   => $doc->name,
						'icon'   => 'book',
						'drawer' => 'lab/docs/' . $doc->name
					]
				];
			}

			if ($github) {
				$buttons[] = [
					'props' => [
						'icon'   => 'github',
						'link'   => $github,
						'target' => '_blank'
					]
				];
			}

			return [
				'component' => 'k-lab-playground-view',
				'breadcrumb' => [
					[
						'label' => $category->name(),
					],
					[
						'label' => $example->title(),
						'link'  => $example->url()
					]
				],
				'props' => [
					'buttons'  => $buttons,
					'compiler' => $compiler,
					'docs'     => $doc?->name,
					'examples' => $vue['examples'],
					'file'     => $example->module(),
					'github'   => $github,
					'props'    => $props,
					'styles'   => $vue['style'],
					'tab'      => $example->tab(),
					'tabs'     => array_values($example->tabs()),
					'template' => $vue['template'],
					'title'    => $example->title(),
				],
			];
		}
	]
];
