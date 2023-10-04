<?php

use Kirby\Panel\Lab\Example;

return [
	'ui' => [
		'pattern' => 'ui',
		'action'  => function () {
			$examples = Example::all();

			return [
				'component' => 'k-ui-index-view',
				'props' => [
					'examples' => $examples
				],
			];
		}
	],
	'ui.vue' => [
		'pattern' => [
			'ui/(:any)/index.vue',
			'ui/(:any)/(:any)/index.vue'
		],
		'action'  => function (string $id, string|null $tab = null) {
			$example = new Example(
				id: $id,
				tab: $tab
			);

			return $example->serve();
		}
	],
	'ui.example' => [
		'pattern' => 'ui/(:any)/(:any?)',
		'action'  => function (string $id, string|null $tab = null) {
			$example = new Example(
				id: $id,
				tab: $tab
			);

			$props = $example->props();
			$vue   = $example->vue();

			return [
				'component' => 'k-ui-playground-view',
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
