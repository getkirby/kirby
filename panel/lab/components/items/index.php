<?php

use Kirby\Toolkit\A;
use Kirby\Toolkit\Str;

return [
	'docs' => 'k-items',
	'items' => A::map(range(0, 10), function ($item) {
		return [
			'id'   => $item,
			'text' => 'This is item ' . $item,
			'info' => 'Some info text',
			'image' => [
				'src' => 'https://picsum.photos/800/600/?v=' . Str::random()
			],
			'options' => [
				[
					'text' => 'Edit',
					'icon' => 'edit'
				],
				[
					'text' => 'Duplicate',
					'icon' => 'copy'
				],
				'-',
				[
					'text' => 'Delete',
					'icon' => 'trash'
				]
			]
		];
	})
];
