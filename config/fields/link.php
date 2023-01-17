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
				'pattern' => 'model',
				'action'  => function () {
					$id   = get('id');
					$type = get('type');

					$model = match ($type) {
						'file' => kirby()->file($id),
						'page' => kirby()->page($id),
						default => site()
					};

					return [
						'title' => $model instanceof File ? $model->filename() : $model->title()->value(),
						'uuid'  => $model->uuid()->toString(),
						'image' => $model->panel()->image([
							'cover'  => true,
							'layout' => 'list'
						]),
					];
				}
			],
			[
				'pattern' => 'finder',
				'action'  => function () {
					$result   = [];
					$parentId = get('parent');
					$type     = get('type');
					$parent   = match (true) {
						empty($parentId)
							=> site(),
						str_starts_with($parentId, 'file://') === true
							=> kirby()->file($parentId)->parent(),
						default
							=> kirby()->page($parentId)
					};

					if (empty($parent) === true) {
						$parent = site();
					}

					$render = function ($item) {
						return match (true) {
							$item instanceof File => [
								'icon'     => 'file',
								'title'    => $item->filename(),
								'uuid'     => $item->uuid()->toString(),
								'children' => false,
								'type'     => 'file',
							],
							default => [
								'icon'     => 'folder',
								'title'    => $item->title()->value(),
								'uuid'     => $item->uuid()->toString(),
								'children' => $item->hasChildren() || $item->hasDrafts() || $item->hasFiles(),
								'type'     => 'page',
							],
						};
					};

					if ($parent instanceof File === false) {
						foreach ($parent->childrenAndDrafts()->sortBy('title', 'asc') as $page) {
							// doesn't make sense to link to the error page
							if ($page->isErrorPage()) {
								continue;
							}

							$result[] = $render($page);
						}

						// only add files for the files browser
						if ($type === 'file') {
							foreach ($parent->files()->sortBy('filename', 'asc') as $file) {
								$result[] = $render($file);
							}
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
							'uuid'  => $grandParent instanceof Site ? '' : $grandParent->uuid()->toString(),
							'root'  => $parent instanceof Site ? true : false
						],
						'children' => $result,
					];
				}
			]
		];
	}
];
