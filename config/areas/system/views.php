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
			$debugMode    = $kirby->option('debug', false) === true;
			$isLocal      = $system->isLocal();

			$environment = [
				[
					'label'  => $license->status()->label(),
					'value'  => $license->label(),
					'theme'  => $license->status()->theme(),
					'icon'   => $license->status()->icon(),
					'dialog' => $license->status()->dialog()
				],
				[
					'label' => $updateStatus?->label() ?? I18n::translate('version'),
					'value' => $kirby->version(),
					'link'  => $updateStatus?->url() ??
						'https://github.com/getkirby/kirby/releases/tag/' . $kirby->version(),
					'theme' => $updateStatus?->theme(),
					'icon'  => $updateStatus?->icon() ?? 'info'
				],
				[
					'label' => 'PHP',
					'value' => phpversion(),
					'icon'  => 'code'
				],
				[
					'label' => I18n::translate('server'),
					'value' => $system->serverSoftwareShort() ?? '?',
					'icon'  => 'server'
				]
			];

			$exceptions = $updateStatus?->exceptionMessages() ?? [];

			$plugins = $system->plugins()->values(function ($plugin) use (&$exceptions) {
				$authors      = $plugin->authorsNames();
				$updateStatus = $plugin->updateStatus();
				$version      = $updateStatus?->toArray();
				$version    ??= $plugin->version() ?? '–';

				if ($updateStatus !== null) {
					$exceptions = [
						...$exceptions,
						...$updateStatus->exceptionMessages()
					];
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

			if ($isLocal === true) {
				$security[] = [
					'id'    => 'local',
					'icon'  => 'info',
					'theme' => 'info',
					'text'  => I18n::translate('system.issues.local')
				];
			}

			if ($debugMode === true) {
				$security[] = [
					'id'    => 'debug',
					'icon'  => $isLocal ? 'info' : 'alert',
					'theme' => $isLocal ? 'info' : 'negative',
					'text'  => I18n::translate('system.issues.debug'),
					'link'  => 'https://getkirby.com/security/debug'
				];
			}

			if (
				$isLocal === false &&
				$kirby->environment()->https() !== true
			) {
				$security[] = [
					'id'   => 'https',
					'text' => I18n::translate('system.issues.https'),
					'link' => 'https://getkirby.com/security/https'
				];
			}

			if ($kirby->option('panel.vue.compiler', null) === null) {
				$security[] = [
					'id'    => 'vue-compiler',
					'link'  => 'https://getkirby.com/security/vue-compiler',
					'text'  => I18n::translate('system.issues.vue.compiler'),
					'theme' => 'notice'
				];
			}

			// sensitive URLs
			if ($isLocal === false) {
				$sensitive = [
					'content' => $system->exposedFileUrl('content'),
					'git'     => $system->exposedFileUrl('git'),
					'kirby'   => $system->exposedFileUrl('kirby'),
					'site'    => $system->exposedFileUrl('site')
				];
			}

			return [
				'component' => 'k-system-view',
				'props'     => [
					'environment' => $environment,
					'exceptions'  => $debugMode ? $exceptions : [],
					'info'        => $system->info(),
					'plugins'     => $plugins,
					'security'    => $security,
					'urls'        => $sensitive ?? null
				]
			];
		}
	],
];
