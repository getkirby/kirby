<?php

use Kirby\Cms\App;
use Kirby\Toolkit\Pagination;

return [
	'props' => [
		/**
		 * Sets the number of items per page. If there are more items the pagination navigation will be shown at the bottom of the section.
		 */
		'limit' => function (int $limit = 20) {
			return $limit;
		},
		/**
		 * Sets the default page for the pagination.
		 */
		'page' => function (int|null $page = null) {
			return App::instance()->request()->get('page', $page);
		},
	],
	'methods' => [
		'pagination' => function () {
			$pagination = new Pagination([
				'limit' => $this->limit,
				'page'  => $this->page,
				'total' => $this->total
			]);

			return [
				'limit'  => $pagination->limit(),
				'offset' => $pagination->offset(),
				'page'   => $pagination->page(),
				'total'  => $pagination->total(),
			];
		}
	]
];
