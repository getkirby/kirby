<?php

use Kirby\Cms\File;
use Kirby\Form\Form;

/**
 * File
 */
return [
	'fields' => [
		'blueprint'  => fn (File $file) => $file->blueprint(),
		'content'    => fn (File $file) => Form::for($file)->toFormValues(),
		'dimensions' => fn (File $file) => $file->dimensions()->toArray(),
		'dragText'   => fn (File $file) => $file->panel()->dragText(),
		'exists'     => fn (File $file) => $file->exists(),
		'extension'  => fn (File $file) => $file->extension(),
		'filename'   => fn (File $file) => $file->filename(),
		'id'         => fn (File $file) => $file->id(),
		'link'       => fn (File $file) => $file->panel()->url(true),
		'mime'       => fn (File $file) => $file->mime(),
		'modified'   => fn (File $file) => $file->modified('c'),
		'name'       => fn (File $file) => $file->name(),
		'next'       => function (File $file) {
			$next = $file->next();

			if ($next === null || $next->isListable() === false) {
				return null;
			}

			return $next;
		},
		'nextWithTemplate' => function (File $file) {
			$files = $file->templateSiblings()->sorted()->filter('isListable', true);
			$index = $files->indexOf($file);

			return $files->nth($index + 1);
		},
		'niceSize'   => fn (File $file) => $file->niceSize(),
		'options'    => fn (File $file) => $file->panel()->options(),
		'panelImage' => fn (File $file) => $file->panel()->image(),
		'panelUrl'   => fn (File $file) => $file->panel()->url(true),
		'prev'       => function (File $file) {
			$prev = $file->prev();

			if ($prev === null || $prev->isListable() === false) {
				return null;
			}

			return $prev;
		},
		'prevWithTemplate' => function (File $file) {
			$files = $file->templateSiblings()->sorted()->filter('isListable', true);
			$index = $files->indexOf($file);

			return $files->nth($index - 1);
		},
		'parent'     => function (File $file) {
			$parent = $file->parent();

			if ($parent === null || $parent->isListable() === false) {
				return null;
			}

			return $parent;
		},
		'parents'    => fn (File $file) => $file->parents()->flip()->filter('isListable', true),
		'size'       => fn (File $file) => $file->size(),
		'template'   => fn (File $file) => $file->template(),
		'thumbs'     => function ($file) {
			if ($file->isResizable() === false) {
				return null;
			}

			return [
				'tiny'   => $file->resize(128)->url(),
				'small'  => $file->resize(256)->url(),
				'medium' => $file->resize(512)->url(),
				'large'  => $file->resize(768)->url(),
				'huge'   => $file->resize(1024)->url(),
			];
		},
		'type'       => fn (File $file) => $file->type(),
		'url'        => fn (File $file) => $file->url(),
		'uuid'       => fn (File $file) => $file->uuid()?->toString()
	],
	'type'  => File::class,
	'views' => [
		'default' => [
			'content',
			'dimensions',
			'exists',
			'extension',
			'filename',
			'id',
			'link',
			'mime',
			'modified',
			'name',
			'next' => 'compact',
			'niceSize',
			'parent' => 'compact',
			'options',
			'prev' => 'compact',
			'size',
			'template',
			'type',
			'url',
			'uuid'
		],
		'compact' => [
			'filename',
			'id',
			'link',
			'type',
			'url',
			'uuid'
		],
		'panel' => [
			'blueprint',
			'content',
			'dimensions',
			'extension',
			'filename',
			'id',
			'link',
			'mime',
			'modified',
			'name',
			'nextWithTemplate' => 'compact',
			'niceSize',
			'options',
			'panelIcon',
			'panelImage',
			'parent' => 'compact',
			'parents' => ['id', 'slug', 'title'],
			'prevWithTemplate' => 'compact',
			'template',
			'type',
			'url',
			'uuid'
		]
	],
];
