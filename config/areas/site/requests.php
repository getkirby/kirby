<?php

use Kirby\Cms\App;
use Kirby\Cms\Find;
use Kirby\Toolkit\I18n;

return [
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
