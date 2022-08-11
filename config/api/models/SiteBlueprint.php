<?php

use Kirby\Cms\SiteBlueprint;

/**
 * SiteBlueprint
 */
return [
	'fields' => [
		'name'    => fn (SiteBlueprint $blueprint) => $blueprint->name(),
		'options' => fn (SiteBlueprint $blueprint) => $blueprint->options(),
		'tabs'    => fn (SiteBlueprint $blueprint) => $blueprint->tabs(),
		'title'   => fn (SiteBlueprint $blueprint) => $blueprint->title(),
	],
	'type'  => 'Kirby\Cms\SiteBlueprint',
	'views' => [],
];
