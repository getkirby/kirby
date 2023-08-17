<?php

use Kirby\Cms\UserBlueprint;

/**
 * UserBlueprint
 */
return [
	'fields' => [
		'name'    => fn (UserBlueprint $blueprint) => $blueprint->name(),
		'options' => fn (UserBlueprint $blueprint) => $blueprint->options(),
		'tabs'    => fn (UserBlueprint $blueprint) => $blueprint->tabs(),
		'title'   => fn (UserBlueprint $blueprint) => $blueprint->title(),
	],
	'type'  => UserBlueprint::class,
	'views' => [],
];
