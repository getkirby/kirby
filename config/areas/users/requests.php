<?php

use Kirby\Cms\App;
use Kirby\Cms\Find;
use Kirby\Panel\Controller\Changes;

$files = require __DIR__ . '/../files/requests.php';

return [
	// User Changes
	'user.changes.discard' => [
		'pattern' => 'users/(:any)/changes/discard',
		'method'  => 'POST',
		'action'  => function (string $path) {
			return Changes::discard(
				model: Find::user($path),
			);
		}
	],
	'user.changes.publish' => [
		'pattern' => 'users/(:any)/changes/publish',
		'method'  => 'POST',
		'action'  => function (string $path) {
			return Changes::publish(
				model: Find::user($path),
				input: App::instance()->request()->get()
			);
		}
	],
	'user.changes.save' => [
		'pattern' => 'users/(:any)/changes/save',
		'method'  => 'POST',
		'action'  => function (string $path) {
			return Changes::save(
				model: Find::user($path),
				input: App::instance()->request()->get()
			);
		}
	],
	'user.changes.unlock' => [
		'pattern' => 'users/(:any)/changes/unlock',
		'method'  => 'POST',
		'action'  => function (string $path) {
			return Changes::unlock(
				model: Find::user($path),
			);
		}
	],

	// User File Changes
	'user.file.changes.discard' => [
		...$files['changes.discard'],
		'pattern' => '(users/.*?)/files/(:any)/changes/discard',
	],
	'user.file.changes.publish' => [
		...$files['changes.publish'],
		'pattern' => '(users/.*?)/files/(:any)/changes/publish',
	],
	'user.file.changes.save' => [
		...$files['changes.save'],
		'pattern' => '(users/.*?)/files/(:any)/changes/save',
	],
	'user.file.changes.unlock' => [
		...$files['changes.unlock'],
		'pattern' => '(users/.*?)/files/(:any)/changes/unlock',
	],
];
