<?php

// @codeCoverageIgnoreStart
return [
	'routes' => function ($kirby) {
		return [
			[
				'pattern' => 'query',
				'method'  => 'POST|GET',
				'auth'    => $kirby->option('kql.auth') !== false,
				'action'  => function () use ($kirby) {
					$kql = '\Kirby\Kql\Kql';

					if (class_exists($kql) === false) {
						return [
							'code'    => 500,
							'status'  => 'error',
							'message' => 'KQL plugin is not installed',
						];
					}

					$input  = $kirby->request()->get();
					$result = $kql::run($input);

					return [
						'code'   => 200,
						'result' => $result,
						'status' => 'ok',
					];
				}
			]
		];
	}
];
// @codeCoverageIgnoreEnd
