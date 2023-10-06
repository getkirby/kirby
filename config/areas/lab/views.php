<?php

use Kirby\Panel\Lab\Example;

return [
	'lab' => [
		'pattern' => 'lab',
		'action'  => function () {
			$examples = Example::all();

			return [
				'component' => 'k-lab-index-view',
				'props' => [
					'examples' => $examples
				],
			];
		}
	],
	'lab.vue' => [
		'pattern' => [
			'lab/(:any)/index.vue',
			'lab/(:any)/(:any)/index.vue'
		],
		'action'  => function (string $id, string|null $tab = null) {
			$example = new Example(
				id: $id,
				tab: $tab
			);

			return $example->serve();
		}
	],
	'lab.example' => [
		'pattern' => 'lab/(:any)/(:any?)',
		'action'  => function (string $id, string|null $tab = null) {
			$example = new Example(
				id: $id,
				tab: $tab
			);

			$props = $example->props();
			$vue   = $example->vue();

			return [
				'component' => 'k-lab-playground-view',
				'breadcrumb' => [
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
