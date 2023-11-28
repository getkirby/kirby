<?php

return [
	'docs' => 'k-browser',
	'items' => A::map(range(1, 10), function ($item) {
		return [
			'image' => [
				'src' => 'https://picsum.photos/100/100/?v=' . Str::random()
			],
			'label' => 'some-image-' . $item . '.jpg',
			'value' => $item,
		];
	})
];
