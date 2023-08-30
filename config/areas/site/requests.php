<?php

use Kirby\Cms\Find;

return [
	'tree' => [
		'pattern' => 'site/tree',
		'action'  => function () {
			$parent = Find::parent(get('parent', 'site'));
			$move   = get('move') ? Find::parent(get('move')) : null;
			$pages  = [];

			foreach ($parent->childrenAndDrafts() as $child) {
				$panel = $child->panel();

				$pages[] = [
					'children'    => $panel->url(true),
					'disabled'    => $move !== null && $move->isMovableTo($child) === false,
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
