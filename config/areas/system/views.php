<?php

use Kirby\Cms\App;
use Kirby\Toolkit\I18n;

return [
	'system' => [
		'pattern' => 'system',
		'action'  => function () {
			$kirby   = App::instance();
			$system  = $kirby->system();
			$license = $system->license();

			$environment = [
				[
					'label'  => I18n::translate('license'),
					'value'  => $license ? 'Kirby 3' : I18n::translate('license.unregistered.label'),
					'theme'  => $license ? null : 'negative',
					'dialog' => $license ? 'license' : 'registration'
				],
				[
					'label' => I18n::translate('version'),
					'value' => $kirby->version(),
					'link'  => 'https://github.com/getkirby/kirby/releases/tag/' . $kirby->version()
				],
				[
					'label' => 'PHP',
					'value' => phpversion()
				],
				[
					'label' => I18n::translate('server'),
					'value' => $system->serverSoftware() ?? '?'
				]
			];

			$plugins = $system->plugins()->values(function ($plugin) {
				$authors = $plugin->authorsNames();
				return [
					'author'  => empty($authors) ? '–' : $authors,
					'license' => $plugin->license() ?? '–',
					'name'    => [
						'text' => $plugin->name() ?? '–',
						'href' => $plugin->link(),
					],
					'version' => $plugin->version() ?? '–',
				];
			});

			$security = [];

			if ($kirby->option('debug', false) === true) {
				$security[] = [
					'id'   => 'debug',
					'text' => I18n::translate('system.issues.debug'),
					'link' => 'https://getkirby.com/security/debug'
				];
			}

			if ($kirby->environment()->https() !== true) {
				$security[] = [
					'id'   => 'https',
					'text' => I18n::translate('system.issues.https'),
					'link' => 'https://getkirby.com/security/https'
				];
			}

			return [
				'component' => 'k-system-view',
				'props'     => [
					'environment' => $environment,
					'plugins'     => $plugins,
					'security'    => $security,
					'urls'        => [
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
