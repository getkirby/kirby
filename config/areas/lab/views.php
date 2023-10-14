<?php

use Kirby\Panel\Lab\Category;
use Kirby\Panel\Lab\Docs;

return [
	'lab' => [
		'pattern' => 'lab',
		'action'  => function () {
			return [
				'component' => 'k-lab-index-view',
				'props' => [
					'tab'        => 'examples',
					'categories' => Category::all(),
				],
			];
		}
	],
	'lab.docs' => [
		'pattern' => 'lab/docs',
		'action'  => function () {
			return [
				'component' => 'k-lab-index-view',
				'title'     => 'Docs',
				'breadcrumb' => [
					[
						'label' => 'Docs',
						'link'  => 'lab/docs'
					]
				],
				'props' => [
					'tab'        => 'docs',
					'categories' => [
						['examples' => Docs::all()]
					],
				],
			];
		}
	],
	'lab.doc' => [
		'pattern' => 'lab/docs/(:any)',
		'action'  => function (string $component) {
			$docs = new Docs($component);

			return [
				'component' => 'k-lab-docs-view',
				'title'     => $component,
				'breadcrumb' => [
					[
						'label' => 'Docs',
						'link'  => 'lab/docs'
					],
					[
						'label' => $component,
						'link'  => 'lab/docs/' . $component
					]
				],
				'props' => [
					'component' => $component,
					'docs'      => $docs->toArray(),
					'lab'       => $docs->lab()
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
					'docs'     => $props['docs'] ?? null,
					'examples' => $vue['examples'],
					'file'     => $example->module(),
					'github'   => $example->github(),
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
