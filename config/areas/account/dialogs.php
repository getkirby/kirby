<?php

use Kirby\Cms\App;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\NotFoundException;
use Kirby\Image\QrCode;
use Kirby\Toolkit\I18n;
use Kirby\Toolkit\Totp;

$dialogs = require __DIR__ . '/../users/dialogs.php';

return [

	// change email
	'account.changeEmail' => [
		'pattern' => '(account)/changeEmail',
		'load'    => $dialogs['user.changeEmail']['load'],
		'submit'  => $dialogs['user.changeEmail']['submit'],
	],

	// change language
	'account.changeLanguage' => [
		'pattern' => '(account)/changeLanguage',
		'load'    => $dialogs['user.changeLanguage']['load'],
		'submit'  => $dialogs['user.changeLanguage']['submit'],
	],

	// change name
	'account.changeName' => [
		'pattern' => '(account)/changeName',
		'load'    => $dialogs['user.changeName']['load'],
		'submit'  => $dialogs['user.changeName']['submit'],
	],

	// change password
	'account.changePassword' => [
		'pattern' => '(account)/changePassword',
		'load'    => $dialogs['user.changePassword']['load'],
		'submit'  => $dialogs['user.changePassword']['submit'],
	],

	// change role
	'account.changeRole' => [
		'pattern' => '(account)/changeRole',
		'load'    => $dialogs['user.changeRole']['load'],
		'submit'  => $dialogs['user.changeRole']['submit'],
	],

	// delete
	'account.delete' => [
		'pattern' => '(account)/delete',
		'load'    => $dialogs['user.delete']['load'],
		'submit'  => $dialogs['user.delete']['submit'],
	],

	// account fields dialogs
	'account.fields' => [
		'pattern' => '(account)/files/(:any)/fields/(:any)/(:all?)',
		'load'    => $dialogs['user.fields']['load'],
		'submit'  => $dialogs['user.fields']['submit']
	],

	// change file name
	'account.file.changeName' => [
		'pattern' => '(account)/files/(:any)/changeName',
		'load'    => $dialogs['user.file.changeName']['load'],
		'submit'  => $dialogs['user.file.changeName']['submit'],
	],

	// change file sort
	'account.file.changeSort' => [
		'pattern' => '(account)/files/(:any)/changeSort',
		'load'    => $dialogs['user.file.changeSort']['load'],
		'submit'  => $dialogs['user.file.changeSort']['submit'],
	],

	// change file template
	'account.file.changeTemplate' => [
		'pattern' => '(account)/files/(:any)/changeTemplate',
		'load'    => $dialogs['user.file.changeTemplate']['load'],
		'submit'  => $dialogs['user.file.changeTemplate']['submit'],
	],

	// delete
	'account.file.delete' => [
		'pattern' => '(account)/files/(:any)/delete',
		'load'    => $dialogs['user.file.delete']['load'],
		'submit'  => $dialogs['user.file.delete']['submit'],
	],

	// account file fields dialogs
	'account.file.fields' => [
		'pattern' => '(account)/files/(:any)/fields/(:any)/(:all?)',
		'load'    => $dialogs['user.file.fields']['load'],
		'submit'  => $dialogs['user.file.fields']['submit']
	],

	// account activate TOTP
	'account.totp.activate' => [
		'pattern' => '(account)/totp/activate',
		'load' => function () {
			$kirby  = App::instance();
			$user   = $kirby->user();
			$totp   = new Totp();
			$issuer = $kirby->site()->title();
			$label  = $user->email();
			$uri    = $totp->uri($issuer, $label);
			$qr     = new QrCode(data: $uri);

			return [
				'component' => 'k-form-dialog',
				'props' => [
					'fields' => [
						'qr' => [
							'label' => I18n::translate('login.totp.activate.label'),
							'type'  => 'info',
							'text'  => $qr->toSvg(),
							'theme' => 'passive',
							'help'  => I18n::template('login.totp.activate.qr.help', ['secret' => $totp->secret()])
						],
						'secret' => [
							'type' => 'hidden',
						],
						'confirm' => [
							'label'       => I18n::translate('login.totp.activate.confirm.label'),
							'type'        => 'text',
							'counter'     => false,
							'font'        => 'monospace',
							'required'    => true,
							'placeholder' => I18n::translate('login.code.placeholder.totp'),
							'help'        => I18n::translate('login.totp.activate.confirm.help')
						],
					],
					'size' => 'small',
					'submitButton' => [
						'text' => I18n::translate('activate'),
						'icon' => 'lock',
						'theme' => 'notice'
					],
					'value' => [
						'secret' => $totp->secret()
					]
				]
			];
		},
		'submit' => function () {
			$kirby  = App::instance();
			$user   = $kirby->user();
			$secret = $kirby->request()->get('secret');

			if ($confirm = $kirby->request()->get('confirm')) {
				$totp = new Totp($secret);

				if ($totp->verify($confirm) === false) {
					throw new InvalidArgumentException(
						I18n::translate('login.totp.activate.confirm.fail')
					);
				}

				$user->changeTotp($secret);
			} else {
				throw new NotFoundException(
					I18n::translate('login.totp.activate.confirm.missing')
				);
			}

			return [
				'message' => I18n::translate('login.totp.activate.success')
			];
		}
	],

	// account disable TOTP
	'account.totp.disable' => [
		'pattern' => '(account)/totp/disable',
		'load'    => $dialogs['user.totp.disable']['load'],
		'submit'  => $dialogs['user.totp.disable']['submit']
	],
];
