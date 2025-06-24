<?php

return [
	'root' => [
		'name' => 'some/site',
		'pretty_version' => 'v9.8.7',
		'version' => '9.8.7.0',
		'reference' => null,
		'type' => 'project',
		'install_path' => __DIR__ . '/../',
		'aliases' => [],
		'dev' => true,
	],
	'versions' => [
		'getkirby/kirby-test-plugin-composer' => [
			'pretty_version' => 'v5.2.3',
			'version' => '5.2.3.0',
			'reference' => null,
			'type' => 'kirby-plugin',
			'install_path' => __DIR__ . '/../',
			'aliases' => [],
			'dev_requirement' => false,
		],
		'getkirby/kirby-test-plugin-composer-noversionset' => [
			'pretty_version' => '1.0.0+no-version-set',
			'version' => '1.0.0.0',
			'reference' => null,
			'type' => 'kirby-plugin',
			'install_path' => __DIR__ . '/../',
			'aliases' => [],
			'dev_requirement' => false,
		],
	],
];
