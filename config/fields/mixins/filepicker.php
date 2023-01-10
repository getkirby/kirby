<?php

use Kirby\Cms\FilePicker;

return [
	'methods' => [
		'filepicker' => function (array $params = []) {
			// fetch the parent model
			$params['model'] = $this->model();

			return (new FilePicker($params))->toArray();
		}
	]
];
