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
			return new Form(
				fields: $this->fields,
				model: $this->model,
				language: 'current'
			);
		},
		'fields' => function () {
			return $this->form->fields()->toProps();
		}
	],
	'methods' => [
		'errors' => function () {
			$this->form->fill($this->model->content('current')->toArray());
			return $this->form->errors();
		}
	],
	'toArray' => function () {
		return [
			'fields' => $this->fields,
		];
	}
];
