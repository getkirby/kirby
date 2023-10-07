<?php

use Kirby\Cms\App;
use Kirby\Panel\Lab\Category;
use Kirby\Toolkit\Str;

return [
	'lab' => [
		'pattern' => 'lab',
		'action'  => function () {
			return [
				'component' => 'k-lab-index-view',
				'props' => [
					'categories' => Category::all(),
				],
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
					'github'   => Str::after($example->root(), App::instance()->root('kirby')) . ($example->tab() ? '/' . $example->tab() : ''),
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
