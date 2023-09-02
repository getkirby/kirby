<?php

use Kirby\Panel\UsersPicker;

return [
	'methods' => [
		'userpicker' => function (array $params = []) {
			$params['model'] = $this->model();

			return (new UsersPicker($params))->toArray();
		}
	]
];
