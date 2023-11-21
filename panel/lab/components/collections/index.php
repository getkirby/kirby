<?php

use Kirby\Toolkit\A;
use Kirby\Toolkit\Str;

return [
	'docs' => 'k-collection',
	'empty' => [
		'icon' => 'box',
		'text' => 'No items yet'
	],
	'help' => 'This is a help text that can be used to give some context to the section. It can be rather short or very long and can contain <a href="#">links</a> and other <strong>HTML</strong>.',
	'items' => A::map(range(0, 20), function ($item) {
		return [
			'text' => 'This is item ' . $item,
			'info' => 'Some info text',
			'image' => [
				'src' => 'https://picsum.photos/800/600/?v=' . Str::random()
			]
		];
	}),
	'pagination' => [
		'page'  => 3,
		'limit' => 8,
		'total' => 40
	]
];
