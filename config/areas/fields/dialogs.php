<?php

use Kirby\Cms\Find;
use Kirby\Panel\Field;

return [
	'model' => [
		'load' => function (
			string $modelPath,
			string $fieldName,
			string|null $path = null
		) {
			return Field::dialog(
				model:     Find::parent($modelPath),
				fieldName: $fieldName,
				path:      $path,
				method:    'GET'
			);
		},
		'submit'  => function (
			string $modelPath,
			string $fieldName,
			string|null $path = null
		) {
			return Field::dialog(
				model:     Find::parent($modelPath),
				fieldName: $fieldName,
				path:      $path,
				method:    'POST'
			);
		}
	],
	'file' => [
		'load' => function (
			string $modelPath,
			string $filename,
			string $fieldName,
			string|null $path = null
		) {
			return Field::dialog(
				model:     Find::file($modelPath, $filename),
				fieldName: $fieldName,
				path:      $path,
				method:    'GET'
			);
		},
		'submit' => function (
			string $modelPath,
			string $filename,
			string $fieldName,
			string|null $path = null
		) {
			return Field::dialog(
				model:     Find::file($modelPath, $filename),
				fieldName: $fieldName,
				path:      $path,
				method:    'POST'
			);
		}
	],
];
