<?php

use Kirby\Cms\App;
use Kirby\Cms\Find;
use Kirby\Content\VersionId;
use Kirby\Toolkit\I18n;

return [
	'page.save' => [
		'pattern' => 'pages/(:any)',
		'method'  => 'POST',
		'action'  => function (string $path) {
			$kirby    = App::instance();
			$fields   = $kirby->request()->get();
			$page     = Find::page($path);
			$version  = $page->version(VersionId::CHANGES);
			$language = $kirby->language('current');

			if ($version->exists($language) === false) {
				$published = $page->version(VersionId::PUBLISHED)->read($language);

				$version->create(
					language: $language,
					fields: [
						...$published,
						...$fields,
					]
				);
			} else {
				$version->update(
					language: $language,
					fields: $fields,
				);
			}

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
