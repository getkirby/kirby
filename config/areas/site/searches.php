<?php

use Kirby\Cms\App;
use Kirby\Toolkit\Escape;
use Kirby\Toolkit\I18n;

return [
	'pages' => [
		'label' => I18n::translate('pages'),
		'icon'  => 'page',
		'query' => function (string $query = null, int $limit, int $page) {
			$kirby = App::instance();
			$pages = $kirby->site()
				->index(true)
				->search($query)
				->filter('isListable', true)
				->paginate($limit, $page);

			return [
				'results' => $pages->values(fn ($page) => [
					'image' => $page->panel()->image(),
					'text' => Escape::html($page->title()->value()),
					'link' => $page->panel()->url(true),
					'info' => Escape::html($page->id()),
					'uuid' => $page->uuid()->toString(),
				]),
				'pagination' => $pages->pagination()->toArray()
			];
		}
	],
	'files' => [
		'label' => I18n::translate('files'),
		'icon'  => 'image',
		'query' => function (string $query = null, int $limit, int $page) {
			$kirby = App::instance();
			$files = $kirby->site()
				->index(true)
				->filter('isListable', true)
				->files()
				->filter('isListable', true)
				->search($query)
				->paginate($limit, $page);

			return [
				'results' => $files->values(fn ($file) => [
					'image' => $file->panel()->image(),
					'text'  => Escape::html($file->filename()),
					'link'  => $file->panel()->url(true),
					'info'  => Escape::html($file->id()),
					'uuid'  => $file->uuid()->toString(),
				]),
				'pagination' => $files->pagination()->toArray()
			];
		}
	]
];
