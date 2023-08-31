<?php

use Kirby\Cms\Find;

return [
	'tree' => [
		'pattern' => 'site/tree',
		'action'  => function () {
			$request = App::instance()->request();
			$parent  = Find::parent($request->get('parent', 'site'));
			$move    = $request->get('move');
			$move    = $move ? Find::parent($move) : null;
			$pages  = [];

			foreach ($parent->childrenAndDrafts() as $child) {
				$panel = $child->panel();

				$pages[] = [
					'children'    => $panel->url(true),
					'disabled'    => $move?->isMovableTo($child) === false,
					'hasChildren' => $child->hasChildren() === true || $child->hasDrafts() === true,
					'icon'        => $panel->image()['icon'] ?? null,
					'id'          => $child->id(),
					'open'        => false,
					'label'       => $child->title()->value(),
					'uuid'        => $child->uuid()->toString(),
				];
			}

			return $pages;
		},
	],
];
