<?php

use Kirby\Panel\Controller\View\AccountViewController;
use Kirby\Panel\Controller\View\FileViewController;
use Kirby\Panel\Controller\View\ResetPasswordViewController;

return [
	'account' => [
		'pattern' => '(account)',
		'action'  => AccountViewController::class
	],
	'account.file' => [
		'pattern' => '(account)/files/(:any)',
		'action'  => FileViewController::class
	],
	'account.password' => [
		'pattern' => 'reset-password',
		'action'  => ResetPasswordViewController::class
	]
];
