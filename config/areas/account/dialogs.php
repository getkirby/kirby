<?php

use Kirby\Cms\App;
use Kirby\Exception\InvalidArgumentException;
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

	// activate TOTP
	'user.totp.activate' => [
		'pattern' => 'account/totp/activate',
		'load' => function () {
			$kirby  = App::instance();
			$user   = $kirby->user();
			$totp   = new Totp();
			$issuer = $kirby->site()->title();
			$label  = $user->email();
			$qr     = new QrCode(data: $totp->uri($issuer, $label));

			return [
				'component' => 'k-form-dialog',
				'props' => [
					'fields' => [
						'qr' => [
							'label' => 'One-time code',
							'type'  => 'info',
							'text'  => $qr->toSvg(),
							'theme' => 'passive',
							'help'  => 'Scan this QR code or add the secret <code>' . $totp->secret() . '</code> manually to your authenticator app',
						],
						'secret' => [
							'type' => 'hidden',
						],
						'confirm' => [
							'label'       => 'Confirm',
							'type'        => 'text',
							'counter'     => false,
							'font'        => 'monospace',
							'required'    => true,
							'placeholder' => I18n::translate('login.code.placeholder.totp'),
							'help'        => 'by entering the 2FA code from your authenticator app'
						],
					],
					'size' => 'small',
					'submitButton' => [
						'text' => 'Activate',
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
					throw new Exception('Invalid 2FA code');
				}

				$user->totp($secret);
			} else {
				throw new Exception('Please enter the 2FA code');
			}

			return [
				'message' => '2FA via TOTP activated'
			];
		}
	],

	// disable TOTP
	'user.totp.disable' => [
		'pattern' => 'account/totp/disable',
		'load' => fn () => [
			'component' => 'k-form-dialog',
			'props' => [
				'fields' => [
					'password' => [
						'type'  => 'password',
						'label'  => 'Enter your password to disable TOTP',
						'required' => true,
						'counter' => false
					]
				],
				'submitButton' => [
					'text'  => 'Disable TOTP',
					'icon'  => 'protected',
					'theme' => 'negative'
				],
			]
		],
		'submit' => function () {
			$kirby    = App::instance();
			$user     = $kirby->user();
			$password = $kirby->request()->get('password');

			try {
				$user->validatePassword($password);
				$user->totp(false);

				return [
					'message' => 'Removed 2FA via TOTP'
				];

			} catch (Exception $e) {
				throw new InvalidArgumentException($e->getMessage());
			}
		}
	],
];
