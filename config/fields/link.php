<?php

use Kirby\Cms\File;
use Kirby\Cms\Page;
use Kirby\Cms\Site;
use Kirby\Data\Data;

return [
	'props' => [
		'value' => function (string|array $value = null) {
			$value = Data::decode($value, 'yaml');

			$defaults = [
				'type' => 'url',
				'link' => '',
			];

			return array_merge($defaults, $value);
		}
	],
	'api' => function () {
		return [
			[
				'pattern' => 'pages',
				'action'  => function () {
					$result   = [];
					$parentId = get('parent');
					$parent   = match (true) {
						empty($parentId)
							=> site(),
						str_starts_with($parentId, 'file://') === true
							=> kirby()->file($parentId),
						default
							=> kirby()->page($parentId)
					};

					if (empty($parent) === true) {
						return [];
					}

					$render = function ($item) {
						return match (true) {
							$item instanceof File => [
								'icon'     => 'file',
								'title'    => $item->filename(),
								'uuid'     => $item->uuid()->toString(),
								'children' => false,
							],
							default => [
								'icon'     => 'folder',
								'title'    => $item->title()->value(),
								'uuid'     => $item->uuid()->toString(),
								'children' => $item->hasChildren() || $item->hasDrafts() || $item->hasFiles(),
							],
						};
					};

					if ($parent instanceof File === false) {
						foreach ($parent->childrenAndDrafts()->sortBy('title', 'asc') as $page) {
							$result[] = $render($page);
						}

						foreach ($parent->files()->sortBy('filename', 'asc') as $file) {
							$result[] = $render($file);
						}

						$grandParent = $parent->parentModel();
					} else {
						$grandParent = $parent->parent();
					}

					$crumb = [];

					if ($parent instanceof Site === false) {
						foreach ($parent->parents()->flip() as $crumbItem) {
							$crumb[] = $render($crumbItem);
						}

						$crumb[] = $render($parent);
					}

					return [
						'crumb' => $crumb,
						'parent' => [
							'title' => $grandParent->title()->value(),
							'uuid'  => $grandParent instanceof Site ? null : $grandParent->uuid()->toString(),
							'root'  => $parent instanceof Site ? true : false
						],
						'children' => $result,
					];
				}
			]
		];
	}
];
