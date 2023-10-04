<?php

return [
	'empty' => [
		'icon' => 'box',
		'text' => 'No items yet'
	],
	'items' => A::map(range(0,20), function ($item) {
		return [
			'text' => 'This is item ' . $item,
			'info' => 'Some info text',
			'image' => [
				'src' => picsum(800, 600)
			]
		];
	})
];
