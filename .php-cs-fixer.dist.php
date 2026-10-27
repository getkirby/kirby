<?php

use Kirby\PhpCs\Config;
use PhpCsFixer\Finder;

$config = Config::create();

return $config
	->setRules([
		...$config->getRules(),
		// both move class members, which would turn 5.x forward merges
		// into conflicts; enable once those have slowed down
		'ordered_class_elements' => false,
		'php_unit_data_provider_method_order' => false
	])
	->setFinder(
		Finder::create()
			->exclude('dependencies')
			->exclude('panel/node_modules')
			->in(__DIR__)
	);
