<?php

$requests = require __DIR__ . '/../users/requests.php';

return [
	// Account Changes
	'account.changes.discard' => [
		...$requests['user.changes.discard'],
		'pattern' => '(account)/changes/discard',
	],
	'account.changes.publish' => [
		...$requests['user.changes.publish'],
		'pattern' => '(account)/changes/publish',
	],
	'account.changes.save' => [
		...$requests['user.changes.save'],
		'pattern' => '(account)/changes/save',
	],
	'account.changes.unlock' => [
		...$requests['user.changes.unlock'],
		'pattern' => '(account)/changes/unlock',
	],

	// Account File Changes
	'account.file.changes.discard' => [
		...$requests['user.file.changes.discard'],
		'pattern' => '(account)/files/(:any)/changes/discard',
	],
	'account.file.changes.publish' => [
		...$requests['user.file.changes.publish'],
		'pattern' => '(account)/files/(:any)/changes/publish',
	],
	'account.file.changes.save' => [
		...$requests['user.file.changes.save'],
		'pattern' => '(account)/files/(:any)/changes/save',
	],
	'account.file.changes.unlock' => [
		...$requests['user.file.changes.unlock'],
		'pattern' => '(account)/files/(:any)/changes/unlock',
	],
];
