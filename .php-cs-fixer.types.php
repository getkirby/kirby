<?php

use PhpCsFixer\Finder;

/**
 * Shortens fully qualified class names in type declarations and docblocks
 * to the imported or same-namespace name.
 *
 * This runs as a second pass on top of `.php-cs-fixer.dist.php`, because
 * PHP CS Fixer applies one rule set per run and the rule must not reach
 * templates, snippets and fixtures: those run in the global namespace,
 * where the fixer would inject a `use` statement into the middle of the
 * script instead of adding it to a header.
 */

$config = require __DIR__ . '/.php-cs-fixer.dist.php';

$finder = Finder::create()
	->in([__DIR__ . '/src', __DIR__ . '/tests'])
	->filter(
		fn ($file) => preg_match('/^namespace\s/m', $file->getContents()) === 1
	);

return $config
	->setRules([
		...$config->getRules(),
		'fully_qualified_strict_types' => ['import_symbols' => true]
	])
	->setCacheFile('.php-cs-fixer.types.cache')
	->setFinder($finder);
