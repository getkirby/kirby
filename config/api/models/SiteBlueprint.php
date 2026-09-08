<?php

use Kirby\Blueprint\SiteBlueprint;

/**
 * SiteBlueprint
 */
return [
	'fields' => [
		'name'    => fn (SiteBlueprint $blueprint) => $blueprint->name(),
		'options' => fn (SiteBlueprint $blueprint) => $blueprint->options(),
		'tabs'    => fn (SiteBlueprint $blueprint) => array_values($blueprint->tabs()->toArray()),
		'title'   => fn (SiteBlueprint $blueprint) => $blueprint->title(),
	],
	'type'  => SiteBlueprint::class,
	'views' => [],
];
