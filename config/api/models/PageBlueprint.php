<?php

use Kirby\Cms\PageBlueprint;

/**
 * PageBlueprint
 */
return [
	'fields' => [
		'name'    => fn (PageBlueprint $blueprint) => $blueprint->name(),
		'num'     => fn (PageBlueprint $blueprint) => $blueprint->num(),
		'options' => fn (PageBlueprint $blueprint) => $blueprint->options(),
		'preview' => fn (PageBlueprint $blueprint) => $blueprint->preview(),
		'status'  => fn (PageBlueprint $blueprint) => $blueprint->status(),
		'tabs'    => fn (PageBlueprint $blueprint) => $blueprint->tabs(),
		'title'   => fn (PageBlueprint $blueprint) => $blueprint->title(),
	],
	'type'  => PageBlueprint::class,
	'views' => [],
];
