<?php

use Kirby\Cms\FileVersion;

/**
 * FileVersion
 */
return [
	'fields' => [
		'dimensions' => fn (FileVersion $file) => $file->dimensions()->toArray(),
		'exists'     => fn (FileVersion $file) => $file->exists(),
		'extension'  => fn (FileVersion $file) => $file->extension(),
		'filename'   => fn (FileVersion $file) => $file->filename(),
		'id'         => fn (FileVersion $file) => $file->id(),
		'mime'       => fn (FileVersion $file) => $file->mime(),
		'modified'   => fn (FileVersion $file) => $file->modified('c'),
		'name'       => fn (FileVersion $file) => $file->name(),
		'niceSize'   => fn (FileVersion $file) => $file->niceSize(),
		'size'       => fn (FileVersion $file) => $file->size(),
		'type'       => fn (FileVersion $file) => $file->type(),
		'url'        => fn (FileVersion $file) => $file->url(),
	],
	'type'  => FileVersion::class,
	'views' => [
		'default' => [
			'dimensions',
			'exists',
			'extension',
			'filename',
			'id',
			'mime',
			'modified',
			'name',
			'niceSize',
			'size',
			'type',
			'url'
		],
		'compact' => [
			'filename',
			'id',
			'type',
			'url',
		],
		'panel' => [
			'dimensions',
			'extension',
			'filename',
			'id',
			'mime',
			'modified',
			'name',
			'niceSize',
			'template',
			'type',
			'url'
		]
	],
];
