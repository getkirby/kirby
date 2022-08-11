<?php

use Kirby\Cms\App;

return [
	'system' => [
		'pattern' => 'system',
		'action'  => function () {
			$kirby   = App::instance();
			$system  = $kirby->system();
			$license = $system->license();

			// @codeCoverageIgnoreStart
			if ($license === true) {
				// valid license, but user is not admin
				$license = 'Kirby 3';
			} elseif ($license === false) {
				// no valid license
				$license = null;
			}
			// @codeCoverageIgnoreEnd

			$plugins = $system->plugins()->values(function ($plugin) {
				return [
					'author'  => $plugin->authorsNames(),
					'license' => $plugin->license(),
					'name'    => [
						'text' => $plugin->name(),
						'href' => $plugin->link(),
					],
					'version' => $plugin->version(),
				];
			});

			return [
				'component' => 'k-system-view',
				'props'     => [
					'debug'   => $kirby->option('debug', false),
					'license' => $license,
					'plugins' => $plugins,
					'php'     => phpversion(),
					'server'  => $system->serverSoftware(),
					'https'   => $kirby->environment()->https(),
					'version' => $kirby->version(),
					'urls'    => [
						'content' => $system->exposedFileUrl('content'),
						'git'     => $system->exposedFileUrl('git'),
						'kirby'   => $system->exposedFileUrl('kirby'),
						'site'    => $system->exposedFileUrl('site')
					]
				]
			];
		}
	],
];
