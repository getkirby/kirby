<?php

$fields = [
	'headline' => [
		'label' => 'Headline',
		'type'  => 'text'
	],
	'text' => [
		'buttons' => ['bold', 'italic', 'link'],
		'label'   => 'Text',
		'size'    => 'large',
		'type'    => 'textarea'
	]
];

$sidebar = [
	'template' => [
		'label'   => 'Template',
		'options' => [
			['text' => 'Default', 'value' => 'default'],
			['text' => 'Gallery', 'value' => 'gallery']
		],
		'type'    => 'select'
	],
	'featured' => [
		'label' => 'Featured',
		'text'  => 'Show on the home page',
		'type'  => 'toggle'
	]
];

return [
	'docs'    => 'k-model-form',
	'api'     => 'pages/photography',
	'columns' => [
		[
			'fields' => $fields,
			'width'  => '2/3'
		],
		[
			'fields' => $sidebar,
			'sticky' => true,
			'width'  => '1/3'
		]
	],
	'content' => [
		'featured' => true,
		'headline' => 'Photography',
		'template' => 'default',
		'text'     => 'A collection of my favourite shots.'
	],
	// the diff only marks which fields have unsaved changes,
	// the values in it are never rendered
	'diff' => [
		'headline' => 'Photography'
	],
	'lock' => [
		'email'    => 'editor@getkirby.com',
		'modified' => '2026-08-14T17:00:00',
		'state'    => 'lock'
	]
];
