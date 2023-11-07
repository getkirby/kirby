<?php

use Kirby\Cms\App;
use Kirby\Panel\Field;
use Kirby\Panel\Panel;
use Kirby\Toolkit\I18n;

return [
	// license key
	'license' => [
		'load' => function () {
			$license = App::instance()->system()->license();

			if ($license === false) {
				go(Panel::url('dialogs/registration'));
			}

			return [
				'component' => 'k-license-dialog',
				'props' => [
					'size'   => 'large',
					'license' => $license,
					// 'fields' => [
					// 	'license' => [
					// 		'type'  => 'info',
					// 		'label' => I18n::translate('license'),
					// 		'text'  => $license ? $license['license'] : I18n::translate('license.unregistered.label'),
					// 		'theme' => $license ? 'code' : 'negative',
					// 		'help'  => $license ?
					// 			// @codeCoverageIgnoreStart
					// 			'<a href="https://hub.getkirby.com" target="_blank">' . I18n::translate('license.manage') . ' &rarr;</a>' :
					// 			// @codeCoverageIgnoreEnd
					// 			'<a href="https://getkirby.com/buy" target="_blank">' . I18n::translate('license.buy') . ' &rarr;</a>'
					// 	]
					// ],
					'submitButton' => false,
					'cancelButton' => false,
				]
			];
		}
	],
	// license registration
	'registration' => [
		'load' => function () {
			$system = App::instance()->system();
			$local  = $system->isLocal();

			return [
				'component' => 'k-form-dialog',
				'props' => [
					'fields' => [
						'domain' => [
							'label' => I18n::translate('license.activate.label'),
							'type'  => 'info',
							'theme' => $local ? 'warning' : 'info',
							'text'  => I18n::template('license.activate.' . ($local ? 'local' : 'domain'), ['host' => $system->indexUrl()])
						],
						'license' => [
							'label'       => I18n::translate('license.code.label'),
							'type'        => 'text',
							'required'    => true,
							'counter'     => false,
							'placeholder' => 'K-',
							'help'        => I18n::translate('license.code.help') . ' ' . '<a href="https://getkirby.com/buy" target="_blank">' . I18n::translate('license.buy') . ' &rarr;</a>'
						],
						'email' => Field::email(['required' => true])
					],
					'submitButton' => [
						'icon'  => 'key',
						'text'  => I18n::translate('activate'),
					],
					'value' => [
						'license' => null,
						'email'   => null
					]
				]
			];
		},
		'submit' => function () {
			// @codeCoverageIgnoreStart
			$kirby = App::instance();
			$kirby->system()->register(
				$kirby->request()->get('license'),
				$kirby->request()->get('email')
			);

			return [
				'event'   => 'system.register',
				'message' => I18n::translate('license.success')
			];
			// @codeCoverageIgnoreEnd
		}
	],
];
