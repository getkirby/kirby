<?php

use Kirby\Cms\App;
use Kirby\Cms\Find;
use Kirby\Panel\Controller\Changes;

/**
 * Shared file requests
 * They are included in the site and
 * users area to create requests there.
 * The array keys are replaced by
 * the appropriate routes in the areas.
 */
return [
	'changes.discard' => [
		'method' => 'POST',
		'action' => function (string $parent, string $filename) {
			return Changes::discard(
				model: Find::file($parent, $filename),
			);
		}
	],
	'changes.publish' => [
		'method'  => 'POST',
		'action'  => function (string $parent, string $filename) {
			return Changes::publish(
				model: Find::file($parent, $filename),
				input: App::instance()->request()->get()
			);
		}
	],
	'changes.save' => [
		'method'  => 'POST',
		'action'  => function (string $parent, string $filename) {
			return Changes::save(
				model: Find::file($parent, $filename),
				input: App::instance()->request()->get()
			);
		}
	],
	'changes.unlock' => [
		'method'  => 'POST',
		'action'  => function (string $parent, string $filename) {
			return Changes::unlock(
				model: Find::file($parent, $filename)
			);
		}
	]
];
