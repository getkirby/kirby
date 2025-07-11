<?php

use Kirby\Panel\Lab\Doc;
use Kirby\Panel\Lab\Docs;

return [
	'lab.docs' => [
		'pattern' => 'lab/docs/(:any)',
		'load'    => function (string $component) {
			if (Docs::isInstalled() === false) {
				return [
					'component' => 'k-text-drawer',
					'props' => [
						'text' => 'The UI docs are not installed.'
					]
				];
			}

			return [
				'component' => 'k-lab-docs-drawer',
				'props' => [
					'icon' => 'book',
					'title' => $component,
					'docs'  => Doc::factory($component)->toArray()
				]
			];
		},
	],
];
