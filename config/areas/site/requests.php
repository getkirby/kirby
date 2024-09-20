<?php

use Kirby\Cms\App;
use Kirby\Cms\Find;
use Kirby\Panel\Controller\Changes;
use Kirby\Toolkit\I18n;

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
	'page.changes.unlock' => [
		'pattern' => 'pages/(:any)/changes/unlock',
		'method'  => 'POST',
		'action'  => function (string $path) {
			return Changes::unlock(
				model: Find::page($path),
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
	'page.file.changes.unlock' => [
		...$files['changes.unlock'],
		'pattern' => '(pages/.*?)/files/(:any)/changes/unlock',
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
	'site.changes.unlock' => [
		'pattern' => 'site/changes/unlock',
		'method'  => 'POST',
		'action'  => function () {
			return Changes::unlock(
				model: App::instance()->site(),
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
	'site.file.changes.unlock' => [
		...$files['changes.unlock'],
		'pattern' => '(site)/files/(:any)/changes/unlock',
	],

	// Tree Navigation
	// @codeCoverageIgnoreStart
	// TODO: move to controller class and add unit tests
	'tree' => [
		'pattern' => 'site/tree',
		'action'  => function () {
			$kirby   = App::instance();
			$request = $kirby->request();
			$move    = $request->get('move');
			$move    = $move ? Find::parent($move) : null;
			$parent  = $request->get('parent');

			if ($parent === null) {
				$site  = $kirby->site();
				$panel = $site->panel();
				$uuid  = $site->uuid()?->toString();
				$url   = $site->url();
				$value = $uuid ?? '/';

				return [
					[
						'children'    => $panel->url(true),
						'disabled'    => $move?->isMovableTo($site) === false,
						'hasChildren' => true,
						'icon'        => 'home',
						'id'          => '/',
						'label'       => I18n::translate('view.site'),
						'open'        => false,
						'url'         => $url,
						'uuid'        => $uuid,
						'value'       => $value
					]
				];
			}

			$parent = Find::parent($parent);
			$pages  = [];

			foreach ($parent->childrenAndDrafts()->filterBy('isListable', true) as $child) {
				$panel = $child->panel();
				$uuid  = $child->uuid()?->toString();
				$url   = $child->url();
				$value = $uuid ?? $child->id();

				$pages[] = [
					'children'    => $panel->url(true),
					'disabled'    => $move?->isMovableTo($child) === false,
					'hasChildren' => $child->hasChildren() === true || $child->hasDrafts() === true,
					'icon'        => $panel->image()['icon'] ?? null,
					'id'          => $child->id(),
					'open'        => false,
					'label'       => $child->title()->value(),
					'url'         => $url,
					'uuid'        => $uuid,
					'value'       => $value
				];
			}

			return $pages;
		}
	],
	'tree.parents' => [
		'pattern' => 'site/tree/parents',
		'action'  => function () {
			$kirby   = App::instance();
			$request = $kirby->request();
			$root    = $request->get('root');
			$page    = $kirby->page($request->get('page'));
			$parents = $page?->parents()->flip()->values(
				fn ($parent) => $parent->uuid()?->toString() ?? $parent->id()
			) ?? [];

			// if root is included, add the site as top-level parent
			if ($root === 'true') {
				array_unshift($parents, $kirby->site()->uuid()?->toString() ?? '/');
			}

			return [
				'data' => $parents
			];
		}
	]
	// @codeCoverageIgnoreEnd
];
