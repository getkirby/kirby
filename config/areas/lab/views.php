<?php

use Kirby\Panel\Lab\Examples;

return [
	'lab' => [
		'pattern' => 'lab',
		'action'  => function () {
			return [
				'component' => 'k-lab-index-view',
				'props' => [
					'examples' => Examples::index(),
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
			return (new Examples($category))->example($id, $tab)->serve();
		}
	],
	'lab.example' => [
		'pattern' => 'lab/(:any)/(:any)/(:any?)',
		'action'  => function (
			string $category,
			string $id,
			string|null $tab = null
		) {
			$examples = new Examples($category);
			$example  = $examples->example($id, $tab);
			$props    = $example->props();
			$vue      = $example->vue();

			return [
				'component' => 'k-lab-playground-view',
				'breadcrumb' => [
					[
						'label' => $examples->name(),
					],
					[
						'label' => $example->title(),
						'link'  => $example->url()
					]
				],
				'props' => [
					'docs'     => $props['docs'] ?? null,
					'examples' => $vue['examples'],
					'title'    => $example->title(),
					'template' => $vue['template'],
					'styles'   => $vue['style'],
					'file'     => $example->module(),
					'props'    => $props,
					'tab'      => $example->tab(),
					'tabs'     => array_values($example->tabs()),
					'template' => $vue['template'],
				],
			];
		}
	]
];
