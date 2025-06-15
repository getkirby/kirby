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

			$doc   = Doc::factory($component);
			$title = $component;

			if ($since = $doc->since) {
				$title .= ' (since ' . $since . ')';
			}

			return [
				'component' => 'k-lab-docs-drawer',
				'props' => [
					'icon' => 'book',
					'title' => $title,
					'docs'  => $doc->toArray()
				]
			];
		},
	],
];
