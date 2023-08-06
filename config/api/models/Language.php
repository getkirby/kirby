<?php

use Kirby\Cms\Language;

/**
 * Language
 */
return [
	'fields' => [
		'code'      => fn (Language $language) => $language->code(),
		'default'   => fn (Language $language) => $language->isDefault(),
		'direction' => fn (Language $language) => $language->direction(),
		'locale'    => fn (Language $language) => $language->locale(),
		'name'      => fn (Language $language) => $language->name(),
		'rules'     => fn (Language $language) => $language->rules(),
		'url'       => fn (Language $language) => $language->url(),
	],
	'type'  => Language::class,
	'views' => [
		'default' => [
			'code',
			'default',
			'direction',
			'locale',
			'name',
			'rules',
			'url'
		]
	]
];
