<?php

use Kirby\PhpCs\Config;
use PhpCsFixer\Finder;

return Config::create()->setFinder(
	Finder::create()
		->exclude('dependencies')
		->exclude('panel/node_modules')
		->in(__DIR__)
);
