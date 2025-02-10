<?php

use Kirby\Cms\App;
use Kirby\Panel\Lab\Category;
use Kirby\Panel\Lab\Docs;

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
			$props = match (Docs::isInstalled()) {
				true => [
					'categories' => [['examples' => Docs::all()]],
					'tab'        => 'docs',
				],
				false => [
					'info' => 'The UI docs are not installed.',
					'tab'  => 'docs',
				]
			};

			return [
				'component' => 'k-lab-index-view',
				'title'     => 'Docs',
				'breadcrumb' => [
					[
						'label' => 'Docs',
						'link'  => 'lab/docs'
					]
				],
				'props' => $props,
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

			$docs = new Docs($component);

			// header buttons
			$buttons = [];

			if ($lab = $docs->lab()) {
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
					'link'   => $docs->github(),
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
					'docs'      => $docs->toArray(),
					'lab'       => $lab
				]
			];
		}
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

			if ($docs = $props['docs'] ?? null) {
				if (
					Docs::isInstalled() === true &&
					Docs::exists($docs) === true
				) {
					$docs = new Docs($docs);
				} else {
					$docs = null;
				}
			}

			$github = $docs?->github();

			if ($source = $props['source'] ?? null) {
				$github ??= 'https://github.com/getkirby/kirby/tree/main/' . $source;
			}

			// header buttons
			$buttons = [];

			if ($docs) {
				$buttons[] = [
					'props' => [
						'text'   => $docs->name(),
						'icon'   => 'book',
						'drawer' => 'lab/docs/' . $docs->name()
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
					'docs'     => $docs?->name(),
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
