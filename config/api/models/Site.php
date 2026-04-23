<?php

use Kirby\Cms\Site;
use Kirby\Form\Form;

/**
 * Site
 */
return [
	'default' => fn () => $this->site(),
	'fields' => [
		'blueprint'  => fn (Site $site) => $site->blueprint(),
		'children'   => fn (Site $site) => $site->children()->filter('isListable', true),
		'content'    => fn (Site $site) => Form::for($site)->toFormValues(),
		'drafts'     => fn (Site $site) => $site->drafts()->filter('isListable', true),
		'files'      => fn (Site $site) => $site->files()->sorted()->filter('isListable', true),
		'options'    => fn (Site $site) => $site->permissions()->toArray(),
		'previewUrl' => fn (Site $site) => $site->previewUrl(),
		'title'      => fn (Site $site) => $site->title()->value(),
		'url'        => fn (Site $site) => $site->url(),
	],
	'type' =>  Site::class,
	'views' => [
		'compact' => [
			'title',
			'url'
		],
		'default' => [
			'content',
			'options',
			'title',
			'url'
		],
		'panel' => [
			'title',
			'blueprint',
			'content',
			'options',
			'previewUrl',
			'url'
		],
		'selector' => [
			'title',
			'children' => [
				'id',
				'title',
				'panelIcon',
				'hasChildren'
			],
		]
	]
];
