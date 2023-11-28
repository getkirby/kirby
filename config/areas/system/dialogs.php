<?php

use Kirby\Cms\App;
use Kirby\Exception\LogicException;
use Kirby\Panel\Field;
use Kirby\Toolkit\I18n;

return [
	// license key
	'license' => [
		'load' => function () {
			$kirby      = App::instance();
			$license    = $kirby->system()->license();
			$obfuscated = $kirby->user()->isAdmin() === false;
			$status     = $license->status();
			$renewable  = $status->renewable();

			return [
				'component' => 'k-license-dialog',
				'props' => [
					'license' => [
						'code'  => $license->code($obfuscated),
						'icon'  => $status->icon(),
						'info'  => $status->info($license->renewal('Y-m-d')),
						'theme' => $status->theme(),
						'type'  => $license->label(),
					],
					'cancelButton' => $renewable,
					'submitButton' => $renewable ? [
						'icon'  => 'refresh',
						'text'  => I18n::translate('renew'),
						'theme' => 'love',
					] : false,
				]
			];
		},
		'submit' => function () {
			// @codeCoverageIgnoreStart
			$response = App::instance()->system()->license()->upgrade();

			// the upgrade is still needed
			if ($response['status'] === 'upgrade') {
				return [
					'redirect' => $response['url']
				];
			}

			// the upgrade has already been completed
			if ($response['status'] === 'complete') {
				return [
					'event'   => 'system.renew',
					'message' => I18n::translate('license.success')
				];
			}

			throw new LogicException('The upgrade failed');
			// @codeCoverageIgnoreEnd
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
						'theme' => 'love',
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
