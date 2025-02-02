<?php

use Kirby\Panel\Ui\Dialogs\SystemActivateDialog;
use Kirby\Panel\Ui\Dialogs\SystemLicenseDialog;

return [
	'license' => [
		'handler' => fn () => new SystemLicenseDialog()
	],
	'registration' => [
		'handler' => fn () => new SystemActivateDialog()
	],
];
