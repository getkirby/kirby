<?php

use Kirby\Blueprint\FileBlueprint;

/**
 * FileBlueprint
 */
return [
	'fields' => [
		'name'    => fn (FileBlueprint $blueprint) => $blueprint->name(),
		'options' => fn (FileBlueprint $blueprint) => $blueprint->options(),
		'tabs'    => fn (FileBlueprint $blueprint) => array_values($blueprint->tabs()->toArray()),
		'title'   => fn (FileBlueprint $blueprint) => $blueprint->title(),
	],
	'type'  => FileBlueprint::class,
	'views' => [],
];
