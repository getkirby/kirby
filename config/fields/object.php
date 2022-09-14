<?php

use Kirby\Data\Data;
use Kirby\Form\Form;

return [
	'props' => [
		'fields' => function (array $fields = []) {
			return $fields;
		}
	],
	'computed' => [
		'fields' => function () {
			if (empty($this->fields) === true) {
				throw new Exception('Please provide some fields for the object');
			}

			return $this->form()->fields()->toArray();
		},
		'value' => function () {
			$data = Data::decode($this->value, 'yaml');

			return $this->form($data)->values();
		}
	],
	'methods' => [
		'form' => function (array $values = []) {
			return new Form([
				'fields' => $this->attrs['fields'],
				'values' => $values,
				'model'  => $this->model
			]);
		},
	],
	'save' => function ($value) {
		return $this->form($value)->values();
	},
];
