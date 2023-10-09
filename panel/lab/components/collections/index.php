<?php

use Kirby\Toolkit\A;
use Kirby\Toolkit\Str;

return [
	'docs' => 'k-collection',
	'empty' => [
		'icon' => 'box',
		'text' => 'No items yet'
	],
	'items' => A::map(range(0, 20), function ($item) {
		return [
			'text' => 'This is item ' . $item,
			'info' => 'Some info text',
			'image' => [
				'src' => 'https://picsum.photos/800/600/?v=' . Str::random()
			]
		];
	})
];
