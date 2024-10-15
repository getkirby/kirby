<?php

use Kirby\Cms\App;
use Kirby\Cms\Find;
use Kirby\Panel\Controller\Changes;
use Kirby\Panel\Controller\PageTree;

$files = require __DIR__ . '/../files/requests.php';

return [
	// Page Changes
	'page.changes.discard' => [
		'pattern' => 'pages/(:any)/changes/discard',
		'method'  => 'POST',
		'action'  => function (string $path) {
			return Changes::discard(
				model: Find::page($path),
			);
		}
	],
	'page.changes.publish' => [
		'pattern' => 'pages/(:any)/changes/publish',
		'method'  => 'POST',
		'action'  => function (string $path) {
			return Changes::publish(
				model: Find::page($path),
				input: App::instance()->request()->get()
			);
		}
	],
	'page.changes.save' => [
		'pattern' => 'pages/(:any)/changes/save',
		'method'  => 'POST',
		'action'  => function (string $path) {
			return Changes::save(
				model: Find::page($path),
				input: App::instance()->request()->get()
			);
		}
	],

	// Page File Changes
	'page.file.changes.discard' => [
		...$files['changes.discard'],
		'pattern' => '(pages/.*?)/files/(:any)/changes/discard',
	],
	'page.file.changes.publish' => [
		...$files['changes.publish'],
		'pattern' => '(pages/.*?)/files/(:any)/changes/publish',
	],
	'page.file.changes.save' => [
		...$files['changes.save'],
		'pattern' => '(pages/.*?)/files/(:any)/changes/save',
	],

	// Site Changes
	'site.changes.discard' => [
		'pattern' => 'site/changes/discard',
		'method'  => 'POST',
		'action'  => function () {
			return Changes::discard(
				model: App::instance()->site(),
			);
		}
	],
	'site.changes.publish' => [
		'pattern' => 'site/changes/publish',
		'method'  => 'POST',
		'action'  => function () {
			return Changes::publish(
				model: App::instance()->site(),
				input: App::instance()->request()->get()
			);
		}
	],
	'site.changes.save' => [
		'pattern' => 'site/changes/save',
		'method'  => 'POST',
		'action'  => function () {
			return Changes::save(
				model: App::instance()->site(),
				input: App::instance()->request()->get()
			);
		}
	],

	// Site File Changes
	'site.file.changes.discard' => [
		...$files['changes.discard'],
		'pattern' => '(site)/files/(:any)/changes/discard',
	],
	'site.file.changes.publish' => [
		...$files['changes.publish'],
		'pattern' => '(site)/files/(:any)/changes/publish',
	],
	'site.file.changes.save' => [
		...$files['changes.save'],
		'pattern' => '(site)/files/(:any)/changes/save',
	],

	// Tree Navigation
	'tree' => [
		'pattern' => 'site/tree',
		'action'  => function () {
			return (new PageTree())->children(
				parent: App::instance()->request()->get('parent'),
				moving: App::instance()->request()->get('move')
			);
		}
	],
	'tree.parents' => [
		'pattern' => 'site/tree/parents',
		'action'  => function () {
			return (new PageTree())->parents(
				page: App::instance()->request()->get('page'),
				includeSite: App::instance()->request()->get('root') === 'true',
			);
		}
	]
];
