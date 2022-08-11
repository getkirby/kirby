<?php

use Kirby\Cms\Helpers;
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
		/**
		 * @deprecated 3.6.0
		 * @todo Remove in 3.8.0
		 * @codeCoverageIgnore
		 */
		'next' => function (Page $page) {
			Helpers::deprecated('The API field page.next has been deprecated and will be removed in 3.8.0.');

			return $page
				->nextAll()
				->filter('intendedTemplate', $page->intendedTemplate())
				->filter('status', $page->status())
				->filter('isReadable', true)
				->first();
		},
		'num'     => fn (Page $page) => $page->num(),
		'options' => fn (Page $page) => $page->panel()->options(['preview']),
		'panelImage' => fn (Page $page) => $page->panel()->image(),
		'parent'     => fn (Page $page) => $page->parent(),
		'parents'    => fn (Page $page) => $page->parents()->flip(),
		/**
		 * @deprecated 3.6.0
		 * @todo Remove in 3.8.0
		 * @codeCoverageIgnore
		 */
		'prev' => function (Page $page) {
			Helpers::deprecated('The API field page.prev has been deprecated and will be removed in 3.8.0.');

			return $page
				->prevAll()
				->filter('intendedTemplate', $page->intendedTemplate())
				->filter('status', $page->status())
				->filter('isReadable', true)
				->last();
		},
		'previewUrl' => fn (Page $page) => $page->previewUrl(),
		'siblings'   => function (Page $page) {
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
