<?php

use Kirby\Cms\PagePicker;

return [
	'methods' => [
		'pagepicker' => function (array $params = []) {
			// inject the current model
			$params['model'] = $this->model();

			return (new PagePicker($params))->toArray();
		}
	]
];
