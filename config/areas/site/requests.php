<?php

use Kirby\Cms\App;
use Kirby\Cms\Find;
use Kirby\Toolkit\I18n;
use Kirby\Toolkit\Str;

return [
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
					'items' => [
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
					]
				];
			}

			$pages    = [];
			$parent   = Find::parent($parent);
			$page     = $request->get('page', 1);
			$limit    = $request->get('limit', 50);
			$children = $parent->childrenAndDrafts();
			$children = $children->filterBy('isListable', true);
			$children = $children->paginate([
				'limit' => $limit,
				'page' => $page
			]);

			foreach ($children as $child) {
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

			return [
				'items'      => $pages,
				'pagination' => $children->pagination()->toArray()
			];
		}
	]
];
