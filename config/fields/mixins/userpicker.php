<?php

use Kirby\Cms\UserPicker;

return [
	'methods' => [
		'userpicker' => function (array $params = []) {
			$params['model'] = $this->model();

			return (new UserPicker($params))->toArray();
		}
	]
];
