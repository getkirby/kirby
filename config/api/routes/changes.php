<?php

use Kirby\Api\Controller\Changes;
use Kirby\Cms\App;
use Kirby\Cms\Find;

// Files
$files = [
	'discard' => [
		'method' => 'POST',
		'action' => function (string $parent, string $filename) {
			return Changes::discard(
				model: Find::file($parent, $filename),
			);
		}
	],
	'publish' => [
		'method'  => 'POST',
		'action'  => function (string $parent, string $filename) {
			return Changes::publish(
				model: Find::file($parent, $filename),
				input: App::instance()->request()->get()
			);
		}
	],
	'save' => [
		'method'  => 'POST',
		'action'  => function (string $parent, string $filename) {
			return Changes::save(
				model: Find::file($parent, $filename),
				input: App::instance()->request()->get()
			);
		}
	]
];

return [
	// Page
	[
		'pattern' => 'pages/(:any)/changes/discard',
		'method'  => 'POST',
		'action'  => function (string $path) {
			return Changes::discard(
				model: Find::page($path),
			);
		}
	],
	[
		'pattern' => 'pages/(:any)/changes/publish',
		'method'  => 'POST',
		'action'  => function (string $path) {
			return Changes::publish(
				model: Find::page($path),
				input: App::instance()->request()->get()
			);
		}
	],
	[
		'pattern' => 'pages/(:any)/changes/save',
		'method'  => 'POST',
		'action'  => function (string $path) {
			return Changes::save(
				model: Find::page($path),
				input: App::instance()->request()->get()
			);
		}
	],

	// Page Files
	[
		...$files['discard'],
		'pattern' => '(pages/.*?)/files/(:any)/changes/discard',
	],
	[
		...$files['publish'],
		'pattern' => '(pages/.*?)/files/(:any)/changes/publish',
	],
	[
		...$files['save'],
		'pattern' => '(pages/.*?)/files/(:any)/changes/save',
	],

	// Site
	[
		'pattern' => 'site/changes/discard',
		'method'  => 'POST',
		'action'  => function () {
			return Changes::discard(
				model: App::instance()->site(),
			);
		}
	],
	[
		'pattern' => 'site/changes/publish',
		'method'  => 'POST',
		'action'  => function () {
			return Changes::publish(
				model: App::instance()->site(),
				input: App::instance()->request()->get()
			);
		}
	],
	[
		'pattern' => 'site/changes/save',
		'method'  => 'POST',
		'action'  => function () {
			return Changes::save(
				model: App::instance()->site(),
				input: App::instance()->request()->get()
			);
		}
	],

	// Site Files
	[
		...$files['discard'],
		'pattern' => '(site)/files/(:any)/changes/discard',
	],
	[
		...$files['publish'],
		'pattern' => '(site)/files/(:any)/changes/publish',
	],
	[
		...$files['save'],
		'pattern' => '(site)/files/(:any)/changes/save',
	],

	// User
	[
		'pattern' => 'users/(:any)/changes/discard',
		'method'  => 'POST',
		'action'  => function (string $path) {
			return Changes::discard(
				model: Find::user($path),
			);
		}
	],
	[
		'pattern' => 'users/(:any)/changes/publish',
		'method'  => 'POST',
		'action'  => function (string $path) {
			return Changes::publish(
				model: Find::user($path),
				input: App::instance()->request()->get()
			);
		}
	],
	[
		'pattern' => 'users/(:any)/changes/save',
		'method'  => 'POST',
		'action'  => function (string $path) {
			return Changes::save(
				model: Find::user($path),
				input: App::instance()->request()->get()
			);
		}
	],

	// User Files
	[
		...$files['discard'],
		'pattern' => '(users/.*?)/files/(:any)/changes/discard',
	],
	[
		...$files['publish'],
		'pattern' => '(users/.*?)/files/(:any)/changes/publish',
	],
	[
		...$files['save'],
		'pattern' => '(users/.*?)/files/(:any)/changes/save',
	],

	// Account
	[
		'pattern' => 'account/changes/discard',
		'method'  => 'POST',
		'action'  => function () {
			return Changes::discard(
				model: App::instance()->user()
			);
		}
	],
	[
		'pattern' => 'account/changes/publish',
		'method'  => 'POST',
		'action'  => function () {
			return Changes::publish(
				model: App::instance()->user(),
				input: App::instance()->request()->get()
			);
		}
	],
	[
		'pattern' => 'account/changes/save',
		'method'  => 'POST',
		'action'  => function () {
			return Changes::save(
				model: App::instance()->user(),
				input: App::instance()->request()->get()
			);
		}
	],

	// Account Files
	[
		...$files['discard'],
		'pattern' => '(account)/files/(:any)/changes/discard',
	],
	[
		...$files['publish'],
		'pattern' => '(account)/files/(:any)/changes/publish',
	],
	[
		...$files['save'],
		'pattern' => '(account)/files/(:any)/changes/save',
	],
];
