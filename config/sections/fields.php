<?php

use Kirby\Form\Fields;

return [
	'props' => [
		'fields' => function (array $fields = []): array {
			return $fields;
		}
	],
	'computed' => [
		'form' => function () {
			return new Fields(
				fields: $this->fields,
				language: $this->model->kirby()->language(),
				model: $this->model
			);
		},
		'fields' => function () {
			return $this->form->toProps();
		}
	],
	'methods' => [
		'errors' => function () {
			return $this->form->errors();
		}
	],
	'toArray' => function () {
		return [
			'fields' => $this->fields,
		];
	}
];
