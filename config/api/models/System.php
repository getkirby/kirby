<?php

use Kirby\Cms\System;
use Kirby\Toolkit\Str;

/**
 * System
 */
return [
	'fields' => [
		'ascii'           => fn () => Str::$ascii,
		'authStatus'      => fn () => $this->kirby()->auth()->status()->toArray(),
		'defaultLanguage' => fn () => $this->kirby()->panelLanguage(),
		'isOk'            => fn (System $system) => $system->isOk(),
		'isInstallable'   => fn (System $system) => $system->isInstallable(),
		'isInstalled'     => fn (System $system) => $system->isInstalled(),
		'isLocal'         => fn (System $system) => $system->isLocal(),
		'multilang'       => fn () => $this->kirby()->option('languages', false) !== false,
		'languages'       => fn () => $this->kirby()->languages(),
		'license'         => fn (System $system) => $system->license(),
		'locales'         => function () {
			$locales = [];
			$translations = $this->kirby()->translations();
			foreach ($translations as $translation) {
				$locales[$translation->code()] = $translation->locale();
			}
			return $locales;
		},
		'loginMethods' => fn (System $system) => array_keys($system->loginMethods()),
		'requirements' => fn (System $system) => $system->toArray(),
		'site'         => fn (System $system) => $system->title(),
		'slugs'        => fn () => Str::$language,
		'title'        => fn () => $this->site()->title()->value(),
		'translation' => function () {
			$code = $this->user()?->language() ??
					$this->kirby()->panelLanguage();

			return
				$this->kirby()->translation($code) ??
				$this->kirby()->translation('en');
		},
		'kirbytext' => fn () => $this->kirby()->option('panel.kirbytext') ?? true,
		'user' => fn () => $this->user(),
		'version' => function () {
			if ($this->user()?->role()->permissions()->for('access', 'system') === true) {
				return $this->kirby()->version();
			}

			return null;
		}
	],
	'type'   => 'Kirby\Cms\System',
	'views'  => [
		'login' => [
			'authStatus',
			'isOk',
			'isInstallable',
			'isInstalled',
			'loginMethods',
			'title',
			'translation'
		],
		'troubleshooting' => [
			'isOk',
			'isInstallable',
			'isInstalled',
			'title',
			'translation',
			'requirements'
		],
		'panel' => [
			'ascii',
			'defaultLanguage',
			'isOk',
			'isInstalled',
			'isLocal',
			'kirbytext',
			'languages',
			'license',
			'locales',
			'multilang',
			'requirements',
			'site',
			'slugs',
			'title',
			'translation',
			'user' => 'auth',
			'version'
		]
	],
];
