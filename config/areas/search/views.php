<?php

return [
	'search' => [
		'pattern' => 'search',
		'action'  => function () {
			return [
				'component' => 'k-search-view',
				'props'     => [
					'query'   => 'hi',
					'results' => ['a' => []]
				]
			];
		}
	],
];
