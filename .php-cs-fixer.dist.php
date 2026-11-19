<?php

use Kirby\PhpCs\Config;
use PhpCsFixer\Finder;

$config = Config::create();

return $config
	->setRules([
		...$config->getRules(),
		'new_expression_parentheses' => true
	])
	->setFinder(
		Finder::create()
			->exclude('dependencies')
			->exclude('panel/node_modules')
			->in(__DIR__)
	);
