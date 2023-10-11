<?php

use Kirby\Panel\Lab\Docs;

return [
	'lab.docs' => [
		'pattern' => 'lab/docs/(:any)',
		'load'    => function (string $component) {
			try {
				$docs = new Docs($component);

				return [
					'component' => 'k-lab-docs-drawer',
					'props' => [
						'icon' => 'book',
						'title' => $component,
						'docs'  => $docs->toArray()
					]
				];
			} catch (Throwable) {
				return [
					'component' => 'k-text-drawer',
					'props' => [
						'icon' => 'book',
						'text'  => "Couldn't find docs for <code>$component</code>"
					]
				];
			}
		},
	],
];
