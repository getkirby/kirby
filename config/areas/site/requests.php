<?php

use Kirby\Cms\App;
use Kirby\Cms\Find;
use Kirby\Content\VersionId;
use Kirby\Form\Form;
use Kirby\Toolkit\I18n;

return [
	'page.changes.discard' => [
		'pattern' => 'pages/(:any)/changes/discard',
		'method'  => 'POST',
		'action'  => function (string $path) {
			Find::page($path)->version(VersionId::changes())->delete();

			return [
				'status' => 'ok'
			];
		}
	],
	'page.changes.publish' => [
		'pattern' => 'pages/(:any)/changes/publish',
		'method'  => 'POST',
		'action'  => function (string $path) {
			$page = Find::page($path);

			// get the changes version
			$changes = $page->version(VersionId::changes());

			// save the submitted changes first
			$changes->save(
				fields: [
					...$page->version(VersionId::published())->read(),
					...App::instance()->request()->get(),
				],
				language: 'current'
			);

			// publish the changes
			$changes->publish(
				language: 'current'
			);

			return [
				'status' => 'ok'
			];
		}
	],
	'page.changes.save' => [
		'pattern' => 'pages/(:any)/changes/save',
		'method'  => 'POST',
		'action'  => function (string $path) {
			$page  = Find::page($path);
			$input = $page->kirby()->request()->get();

			// we need to run the input through the form
			// class to get a set of storable field values
			// that we can send to the content storage handler
			$form = Form::for($page, [
				'ignoreDisabled' => true,
				'input'          => $input,
			]);

			// combine the new field changes with the
			// last published state
			$page->version(VersionId::changes())->save(
				fields: [
					...$page->version(VersionId::published())->read(),
					...$form->strings(),
				],
				language: 'current'
			);

			return [
				'status' => 'ok'
			];
		}
	],
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
	]
];
