<?php

use Kirby\Cms\App;
use Kirby\Kql\Kql;

/**
 * KQL Routes
 */

return [
	[
		'pattern' => 'query',
		'method'  => 'POST|GET',
		'auth'    => App::instance()->option('kql.auth') !== false,
		'action'  => function () {
			$input  = App::instance()->request()->get();
			$result = Kql::run($input);

			return [
				'code'   => 200,
				'result' => $result,
				'status' => 'ok',
			];
		}
	]
];
