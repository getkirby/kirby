<?php

use Kirby\Panel\Controller\Dialog\SystemLicenseActivateDialogController;
use Kirby\Panel\Controller\Dialog\SystemLicenseDialogController;
use Kirby\Panel\Controller\Dialog\SystemLicenseRemoveDialogController;

return [
	'license'        => SystemLicenseDialogController::class,
	'license/remove' => SystemLicenseRemoveDialogController::class,
	'registration'   => SystemLicenseActivateDialogController::class,
];
