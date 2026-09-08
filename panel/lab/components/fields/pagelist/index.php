<?php

use Kirby\Toolkit\A;
use Kirby\Toolkit\Str;

$statuses = ['listed', 'listed', 'unlisted', 'draft', 'listed'];

$pages = A::map(range(1, 5), function ($page) use ($statuses) {
	$status = $statuses[$page - 1];

	return [
		'id'    => 'photography/album-' . $page,
		'image' => [
			'src' => 'https://picsum.photos/800/600/?v=' . Str::random()
		],
		'info'        => 'album',
		'link'        => '/pages/photography+album-' . $page,
		'permissions' => [
			'changeStatus' => true,
			'delete'       => true,
			'sort'         => $status === 'listed'
		],
		'status'   => $status,
		'template' => 'album',
		'text'     => 'Album ' . $page,
		'uuid'     => 'page://' . Str::random(16)
	];
});

// the columns of a table layout are resolved on the server,
// so the lab has to ship them the way `PageListField` sends them
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
	],
	'flag' => [
		'label'  => ' ',
		'mobile' => true,
		'type'   => 'flag',
		'width'  => 'var(--table-row-height)'
	]
];

return [
	'docs'    => 'k-pagelist-field',
	'columns' => $columns,
	'customColumns' => [
		...$columns,
		'template' => [
			'id'    => 'template',
			'label' => 'Template',
			'type'  => 'text'
		]
	],
	'endpoints' => [
		'field' => 'pages/photography/fields/albums'
	],
	'pages'      => $pages,
	'pagination' => [
		'limit'  => 5,
		'offset' => 0,
		'page'   => 1,
		'total'  => 5
	],
	'paginated' => [
		'limit'  => 2,
		'offset' => 0,
		'page'   => 1,
		'total'  => 5
	]
];
