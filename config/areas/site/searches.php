<?php

use Kirby\Cms\App;
use Kirby\Toolkit\Escape;
use Kirby\Toolkit\I18n;

return [
	'pages' => [
		'label' => I18n::translate('pages'),
		'icon'  => 'page',
		'query' => function (string $query = null) {
			$pages = App::instance()->site()
				->index(true)
				->search($query)
				->filter('isReadable', true)
				->limit(10);

			$results = [];

			foreach ($pages as $page) {
				$results[] = [
					'image' => $page->panel()->image(),
					'text' => Escape::html($page->title()->value()),
					'link' => $page->panel()->url(true),
					'info' => Escape::html($page->id())
				];
			}

			return $results;
		}
	],
	'files' => [
		'label' => I18n::translate('files'),
		'icon'  => 'image',
		'query' => function (string $query = null) {
			$files = App::instance()->site()
				->index(true)
				->filter('isReadable', true)
				->files()
				->search($query)
				->limit(10);

			$results = [];

			foreach ($files as $file) {
				$results[] = [
					'image' => $file->panel()->image(),
					'text'  => Escape::html($file->filename()),
					'link'  => $file->panel()->url(true),
					'info'  => Escape::html($file->id())
				];
			}

			return $results;
		}
	]
];
