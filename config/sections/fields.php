<?php

use Kirby\Form\Form;

return [
	'props' => [
		'fields' => function (array $fields = []) {
			return $fields;
		}
	],
	'computed' => [
		'form' => function () {
			return new Form([
				'fields'   => $this->fields,
				'values'   => $this->model->content('current')->toArray(),
				'model'    => $this->model,
				'language' => 'current',
				'strict'   => true
			]);
		},
		'fields' => function () {
			return $this->form->fields()->toProps();
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
