<?php

use Kirby\Toolkit\A;
use Kirby\Toolkit\Str;

return [
	'docs' => 'k-files-picker-dialog',
	'items' => A::map(range(0, 5), function ($item) {
		return [
			'id'    => $item,
			'text'  => 'File ' . $item,
			'image' => [
				'src' => 'https://picsum.photos/800/600/?v=' . Str::random()
			]
		];
	})
];
