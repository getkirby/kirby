<?php

use Kirby\Cms\App;
use Kirby\Toolkit\I18n;

return [
	'system' => [
		'pattern' => 'system',
		'action'  => function () {
			$kirby        = App::instance();
			$system       = $kirby->system();
			$updateStatus = $system->updateStatus();
			$license      = $system->license();

			$environment = [
				[
					'label'  => $license ? I18n::translate('license') : I18n::translate('license.register.label'),
					'value'  => $license ? 'Kirby 3' : I18n::translate('license.unregistered.label'),
					'theme'  => $license ? null : 'negative',
					'dialog' => $license ? 'license' : 'registration'
				],
				[
					'label' => $updateStatus?->label() ?? I18n::translate('version'),
					'value' => $kirby->version(),
					'link'  => (
						$updateStatus ?
						$updateStatus->url() :
						'https://github.com/getkirby/kirby/releases/tag/' . $kirby->version()
					),
					'theme' => $updateStatus?->theme()
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

			$exceptions = $updateStatus?->exceptionMessages() ?? [];

			$plugins = $system->plugins()->values(function ($plugin) use (&$exceptions) {
				$authors      = $plugin->authorsNames();
				$updateStatus = $plugin->updateStatus();
				$version      = $updateStatus?->toArray() ?? $plugin->version() ?? '–';

				if ($updateStatus !== null) {
					$exceptions = array_merge($exceptions, $updateStatus->exceptionMessages());
				}

				return [
					'author'  => empty($authors) ? '–' : $authors,
					'license' => $plugin->license() ?? '–',
					'name'    => [
						'text' => $plugin->name() ?? '–',
						'href' => $plugin->link(),
					],
					'version' => $version,
				];
			});

			$security = $updateStatus?->messages() ?? [];

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
					'exceptions'  => $kirby->option('debug') === true ? $exceptions : [],
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
