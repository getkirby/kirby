<?php

use Kirby\Cms\App;
use Kirby\Panel\Field;
use Kirby\Toolkit\I18n;

return [
	// license key
	'license' => [
		'load' => function () {
			$license = App::instance()->system()->license();

			// @codeCoverageIgnoreStart
			// the system is registered but the license
			// key is only visible for admins
			if ($license === true) {
				$license = 'Kirby 3';
			}
			// @codeCoverageIgnoreEnd

			return [
				'component' => 'k-form-dialog',
				'props' => [
					'size'   => 'medium',
					'fields' => [
						'license' => [
							'type'  => 'info',
							'label' => I18n::translate('license'),
							'text'  => $license ? $license : I18n::translate('license.unregistered.label'),
							'theme' => $license ? 'code' : 'negative',
							'help'  => $license ?
								// @codeCoverageIgnoreStart
								'<a href="https://hub.getkirby.com">' . I18n::translate('license.manage') . ' &rarr;</a>' :
								// @codeCoverageIgnoreEnd
								'<a href="https://getkirby.com/buy">' . I18n::translate('license.buy') . ' &rarr;</a>'
						]
					],
					'submitButton' => false,
					'cancelButton' => false,
				]
			];
		}
	],
	// license registration
	'registration' => [
		'load' => function () {
			return [
				'component' => 'k-form-dialog',
				'props' => [
					'fields' => [
						'license' => [
							'label'       => I18n::translate('license.register.label'),
							'type'        => 'text',
							'required'    => true,
							'counter'     => false,
							'placeholder' => 'K3-',
							'help'        => I18n::translate('license.register.help')
						],
						'email' => Field::email([
							'required' => true
						])
					],
					'submitButton' => I18n::translate('license.register'),
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
				'message' => I18n::translate('license.register.success')
			];
			// @codeCoverageIgnoreEnd
		}
	],
];
