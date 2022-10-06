<?php

use Kirby\Cms\Page;
use Kirby\Form\Form;

/**
 * Page
 */
return [
	'fields' => [
		'blueprint'   => fn (Page $page) => $page->blueprint(),
		'blueprints'  => fn (Page $page) => $page->blueprints(),
		'children'    => fn (Page $page) => $page->children(),
		'content'     => fn (Page $page) => Form::for($page)->values(),
		'drafts'      => fn (Page $page) => $page->drafts(),
		'errors'      => fn (Page $page) => $page->errors(),
		'files'       => fn (Page $page) => $page->files()->sorted(),
		'hasChildren' => fn (Page $page) => $page->hasChildren(),
		'hasDrafts'   => fn (Page $page) => $page->hasDrafts(),
		'hasFiles'    => fn (Page $page) => $page->hasFiles(),
		'id'          => fn (Page $page) => $page->id(),
		'isSortable'  => fn (Page $page) => $page->isSortable(),
		'num'     	  => fn (Page $page) => $page->num(),
		'options' 	  => fn (Page $page) => $page->panel()->options(['preview']),
		'panelImage'  => fn (Page $page) => $page->panel()->image(),
		'parent'      => fn (Page $page) => $page->parent(),
		'parents'     => fn (Page $page) => $page->parents()->flip(),
		'previewUrl'  => fn (Page $page) => $page->previewUrl(),
		'siblings'    => function (Page $page) {
			if ($page->isDraft() === true) {
				return $page->parentModel()->children()->not($page);
			} else {
				return $page->siblings();
			}
		},
		'slug'     => fn (Page $page) => $page->slug(),
		'status'   => fn (Page $page) => $page->status(),
		'template' => fn (Page $page) => $page->intendedTemplate()->name(),
		'title'    => fn (Page $page) => $page->title()->value(),
		'url'      => fn (Page $page) => $page->url(),
	],
	'type' => 'Kirby\Cms\Page',
	'views' => [
		'compact' => [
			'id',
			'title',
			'url',
			'num'
		],
		'default' => [
			'content',
			'id',
			'status',
			'num',
			'options',
			'parent' => 'compact',
			'slug',
			'template',
			'title',
			'url'
		],
		'panel' => [
			'id',
			'blueprint',
			'content',
			'status',
			'options',
			'next'    => ['id', 'slug', 'title'],
			'parents' => ['id', 'slug', 'title'],
			'prev'    => ['id', 'slug', 'title'],
			'previewUrl',
			'slug',
			'title',
			'url'
		],
		'selector' => [
			'id',
			'title',
			'parent' => [
				'id',
				'title'
			],
			'children' => [
				'hasChildren',
				'id',
				'panelIcon',
				'panelImage',
				'title',
			],
		]
	],
];
