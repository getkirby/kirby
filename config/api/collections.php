<?php

/**
 * Api Collection Definitions
 */
return [

	/**
	 * Children
	 */
	'children' => [
		'model' => 'page',
		'type'  => 'Kirby\Cms\Pages',
		'view'  => 'compact'
	],

	/**
	 * Files
	 */
	'files' => [
		'model' => 'file',
		'type'  => 'Kirby\Cms\Files'
	],

	/**
	 * Languages
	 */
	'languages' => [
		'model' => 'language',
		'type'  => 'Kirby\Cms\Languages'
	],

	/**
	 * Pages
	 */
	'pages' => [
		'model' => 'page',
		'type'  => 'Kirby\Cms\Pages',
		'view'  => 'compact'
	],

	/**
	 * Roles
	 */
	'roles' => [
		'model' => 'role',
		'type'  => 'Kirby\Cms\Roles',
		'view'  => 'compact'
	],

	/**
	 * Translations
	 */
	'translations' => [
		'model' => 'translation',
		'type'  => 'Kirby\Cms\Translations',
		'view'  => 'compact'
	],

	/**
	 * Users
	 */
	'users' => [
		'default' => fn () => $this->users(),
		'model'   => 'user',
		'type'    => 'Kirby\Cms\Users',
		'view'    => 'compact'
	]

];
