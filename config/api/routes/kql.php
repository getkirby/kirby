<?php

use Kirby\Cms\App;
use Kirby\Kql\Kql;

return [
	[
		'pattern' => 'query',
		'method'  => 'POST|GET',
		'auth'    => App::instance()->option('kql.auth') !== false,
		'action'  => function () {
			return [
				'code'   => 200,
				'result' => Kql::run($this->kirby()->request()->get()),
				'status' => 'ok',
			];
		}
	]
];
