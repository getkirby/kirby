<?php

use Kirby\Cms\App;
use Kirby\Data\Data;
use Kirby\Toolkit\Str;

return [
	'ui.docs' => [
		'pattern' => 'ui/docs/(:any)',
		'load'    => function (string $component) {

			$kirby = App::instance();
			$file  = $kirby->root('panel') . '/dist/ui.json';
			$json  = Data::read($file);
			$docs  = [];

			// global kirbytext options for all conversions
			$kirbytextOptions = [
				'markdown' => [
					'breaks' => false
				]
			];

			foreach ($json as $entry) {
				$componentName = 'k-'. Str::camelToKebab($entry['displayName']);

				if ($component === $componentName) {
					$docs = $entry;

					$docs['component']   = $componentName;
					$docs['description'] = $kirby->kirbytext($docs['description'] ?? '', $kirbytextOptions);

					if (empty($docs['tags']['examples']) === false) {
						$docs['examples'] = $docs['tags']['examples'];
					}

					// sanitize props
					foreach (($docs['props'] ?? []) as $propKey => $prop) {
						$docs['props'][$propKey]['description'] = $kirby->kirbytext($prop['description'] ?? '', $kirbytextOptions);
					}

					// sanitize slots
					foreach (($docs['slots'] ?? []) as $slotKey => $slot) {
						$docs['slots'][$slotKey]['description'] = $kirby->kirbytext($slot['description'] ?? '', $kirbytextOptions);
					}

					break;
				}
			}

			return [
				'component' => 'k-ui-docs-drawer',
				'props' => [
					'icon' => 'book',
					'title' => $component,
					'docs'  => $docs
				]
			];
		},
	],
];
