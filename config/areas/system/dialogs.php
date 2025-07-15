<?php

use Kirby\Cms\App;
use Kirby\Panel\Ui\Dialogs\SystemActivateDialog;
use Kirby\Panel\Ui\Dialogs\SystemLicenseDialog;
use Kirby\Toolkit\I18n;

return [
	'license' => [
		'handler' => fn () => new SystemLicenseDialog()
	],
	'license/remove' => [
		'load' => function () {
			return [
				'component' => 'k-remove-dialog',
				'props' => [
					'text' => I18n::translate('license.remove.text'),
					'size' => 'medium',
					'submitButton' => [
						'icon'  => 'trash',
						'text'  => I18n::translate('remove'),
						'theme' => 'negative',
					],
				]
			];
		},
		'submit' => function () {
			// @codeCoverageIgnoreStart
			App::instance()->system()->license()->delete();
			return true;
			// @codeCoverageIgnoreEnd
		}
	],
	// license registration
	'registration' => [
		'handler' => fn () => new SystemActivateDialog()
	],
];
