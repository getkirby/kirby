<?php

return [
	'props' => [
		'fields' => function (array $fields = []) {
			return $fields;
		}
	],
	'toArray' => function () {
		return [
			'fields' => $this->fields,
		];
	}
];
