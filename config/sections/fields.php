<?php

use Kirby\Cms\Page;
use Kirby\Cms\Site;
use Kirby\Form\Form;

return [
	'props' => [
		'fields' => function (array $fields = []) {
			return $fields;
		}
	],
	'computed' => [
		'form' => function () {
			$fields   = $this->fields;
			$disabled = $this->model->permissions()->cannot('update');
			$lang     = $this->model->kirby()->languageCode();
			$content  = $this->model->content($lang)->toArray();

			if ($disabled === true) {
				foreach ($fields as $key => $props) {
					$fields[$key]['disabled'] = true;
				}
			}

			return new Form([
				'fields' => $fields,
				'values' => $content,
				'model'  => $this->model,
				'strict' => true
			]);
		},
		'fields' => function () {
			$fields = $this->form->fields()->toArray();

			if (
				$this->model instanceof Page ||
				$this->model instanceof Site
			) {
				// the title should never be updated directly via
				// fields section to avoid conflicts with the rename dialog
				unset($fields['title']);
			}

			foreach ($fields as $index => $props) {
				unset($fields[$index]['value']);
			}

			return $fields;
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
