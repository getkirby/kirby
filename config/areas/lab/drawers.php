<?php

use Kirby\Panel\Lab\Docs;

return [
	'lab.docs' => [
		'pattern' => 'lab/docs/(:any)',
		'load'    => function (string $component) {
			if (Docs::installed() === false) {
				return [
					'component' => 'k-text-drawer',
					'props' => [
						'text' => 'The UI docs are not installed.'
					]
				];
			}

			$docs = new Docs($component);

			return [
				'component' => 'k-lab-docs-drawer',
				'props' => [
					'icon' => 'book',
					'title' => $component,
					'docs'  => $docs->toArray()
				]
			];
		},
	],
];
