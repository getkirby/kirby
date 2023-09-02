<?php

use Kirby\Panel\PagesPicker;

return [
	'methods' => [
		'pagepicker' => function (array $params = []) {
			// inject the current model
			$params['model'] = $this->model();

			return (new PagesPicker($params))->toArray();
		}
	]
];
