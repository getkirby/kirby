<?php

use Kirby\Toolkit\A;
use Kirby\Toolkit\Str;

return [
	'docs' => 'k-pages-picker-dialog',
	'items' => A::map(range(0, 5), function ($item) {
		return [
			'id'    => $item,
			'text'  => 'Page ' . $item,
			'image' => [
				'src' => 'https://picsum.photos/800/600/?v=' . Str::random()
			],
			'hasChildren' => rand(0, 1) === 0
		];
	})
];
