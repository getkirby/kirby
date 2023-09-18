<?php

use Kirby\Cms\App;
use Kirby\Toolkit\Totp;

$dialogs = require __DIR__ . '/../users/dialogs.php';

include_once 'QrCode.php';

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

	// change TOTP status
	'user.changeTotp' => [
		'pattern' => 'account/changeTotp',
		'load' => function () {
			$kirby  = App::instance();
			$user   = $kirby->user();
			$secret = $user->totp();

			if ($secret === null) {
				$totp = new Totp();

				$issuer = $kirby->site()->title();
				$label  = $user->email();
				$uri    = $totp->uri($issuer, $label);

				$qr = new QRCode($uri);
				$image = $qr->render_image();
				ob_start();
				imagepng($image);
				$data = ob_get_contents();
       			ob_end_clean();

				return [
					'component' => 'k-form-dialog',
					'props' => [
						'fields' => [
							'qr' => [
								'label' => 'Scan this QR code',
								'type'  => 'info',
								'text'  => '<img src="data:image/png;base64,' . base64_encode($data) . '" title="' . $uri .'" />',
								'theme' => 'none',
							],
							'secret_display' => [
								'type'  => 'info',
								'text'  => $totp->secret(),
								'theme' => 'passive',
								'help'  => 'or add the 2FA secret manually to your authenticator app',
							],
							'secret' => [
								'type'     => 'hidden',
							],
							'confirm' => [
								'label'   => 'Confirm',
								'type'    => 'text',
								'counter' => false,
								'help'    => 'by entering the 2FA code from your authenticator app'
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
			}

			return [
				'component' => 'k-remove-dialog',
				'props' => [
					'text' => 'Are you sure you want to deactivate TOTP?',
					'submitButton' => [
						'text' => 'Disable',
						'icon' => 'protected'
					],
				]
			];
		},
		'submit' => function () {
			$kirby  = App::instance();
			$user   = $kirby->user();

			if ($secret = $kirby->request()->get('secret')) {
				if ($confirm = $kirby->request()->get('confirm')) {
					$totp = new Totp($secret);

					if ($totp->verify($confirm) === false) {
						throw new Exception('Invalid 2FA code');
					}

					$user->totp($secret);
				}

			} else {
				$user->totp(false);
			}

			return true;
		}
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
];
