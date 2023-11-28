<?php

/**
 * Api Routes Definitions
 */
return function ($kirby) {
	$routes = array_merge(
		include __DIR__ . '/routes/auth.php',
		include __DIR__ . '/routes/pages.php',
		include __DIR__ . '/routes/roles.php',
		include __DIR__ . '/routes/site.php',
		include __DIR__ . '/routes/users.php',
		include __DIR__ . '/routes/files.php',
		include __DIR__ . '/routes/lock.php',
		include __DIR__ . '/routes/system.php',
		include __DIR__ . '/routes/translations.php'
	);

	// only add the language routes if the
	// multi language setup is activated
	if ($kirby->option('languages', false) !== false) {
		$routes = array_merge(
			$routes,
			include __DIR__ . '/routes/languages.php'
		);
	}

	return $routes;
};
