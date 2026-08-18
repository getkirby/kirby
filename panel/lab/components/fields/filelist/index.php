<?php

use Kirby\Toolkit\A;
use Kirby\Toolkit\Str;

$files = A::map(range(1, 7), function ($file) {
	return [
		'id'    => 'photography/image-' . $file . '.jpg',
		'image' => [
			'src' => 'https://picsum.photos/800/600/?v=' . Str::random()
		],
		'info'        => 'image',
		'link'        => '/pages/photography/files/image-' . $file . '.jpg',
		'permissions' => [
			'delete' => true,
			'sort'   => true
		],
		'template' => 'image',
		'text'     => 'image-' . $file . '.jpg',
		'uuid'     => 'file://' . Str::random(16)
	];
});

// the columns of a table layout are resolved on the server,
// so the lab has to ship them the way `ModelListField` sends them
$columns = [
	'image' => [
		'label'  => ' ',
		'mobile' => true,
		'type'   => 'image',
		'width'  => 'var(--table-row-height)'
	],
	'title' => [
		'label'  => 'Title',
		'mobile' => true,
		'type'   => 'url'
	]
];

return [
	'docs'    => 'k-filelist-field',
	'columns' => $columns,
	'customColumns' => [
		...$columns,
		'alt' => [
			'id'    => 'alt',
			'label' => 'Alt text',
			'type'  => 'text'
		],
		'template' => [
			'id'    => 'template',
			'label' => 'Template',
			'type'  => 'text'
		]
	],
	'endpoints' => [
		'field' => 'pages/photography/fields/gallery'
	],
	'files'      => $files,
	'pagination' => [
		'limit'  => 7,
		'offset' => 0,
		'page'   => 1,
		'total'  => 7
	],
	'paginated' => [
		'limit'  => 3,
		'offset' => 0,
		'page'   => 1,
		'total'  => 7
	],
	'upload' => [
		'accept'     => 'image/*',
		'api'        => 'pages/photography/files',
		'attributes' => [],
		'max'        => null,
		'multiple'   => true,
		'preview'    => []
	]
];
