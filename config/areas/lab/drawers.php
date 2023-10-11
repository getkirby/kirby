<?php

use Kirby\Cms\App;
use Kirby\Data\Data;
use Kirby\Panel\Lab\Docs;

return [
	'lab.docs' => [
		'pattern' => 'lab/docs/(:any)',
		'load'    => function (string $component) {
			$kirby = App::instance();
			$file  = $kirby->root('panel') . '/dist/ui.json';
			$json  = Data::read($file);

			foreach ($json as $entry) {
				$docs  = new Docs($entry);
				$name = $docs->name();

				if ($component === $name) {
					return [
						'component' => 'k-lab-docs-drawer',
						'props' => [
							'icon' => 'book',
							'title' => $component,
							'docs'  => $docs->toArray()
						]
					];
				}
			}

			return [
				'component' => 'k-text-drawer',
				'props' => [
					'icon' => 'book',
					'title' => $component,
					'text'  => "Couldn't find the docs for <code>$component</code>"
				]
			];
		},
	],
];
