<?php

use Kirby\Cms\App;
use Kirby\Http\Cookie;
use Kirby\Panel\Ui\Buttons\ViewButtons;
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

			$latestVersion = $updateStatus?->latestVersion();
			$nextMajor     = (int)$latestVersion > (int)$kirby->version() ? (int)$latestVersion : null;

			if ($nextMajor !== null) {
				$issues = [];
				$php    = PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION . '.' . PHP_RELEASE_VERSION;

				if ($updateStatus->supportsPhp($latestVersion, $php) === false) {
					$issues[] = [
						'id'   => 'php',
						'link' => 'https://getkirby.com/docs/guide/quickstart#requirements',
						'text' => I18n::template('system.upgrade.php', null, [
							'kirby' => $nextMajor,
							'php'   => $php
						])
					];
				}

				foreach ($system->plugins() as $plugin) {
					$compatibility = $plugin->updateStatus()?->compatibility($latestVersion);
					$status        = $compatibility['status'] ?? 'unknown';

					if ($status === 'compatible') {
						continue;
					}

					$issues[] = [
						'id'    => 'plugin-' . $plugin->id(),
						'icon'  => match ($status) {
							'update'  => 'info',
							'unknown' => 'question',
							default   => 'alert'
						},
						'link'  => $plugin->link(),
						'text'  => I18n::template('system.upgrade.plugin.' . $status, null, [
							'kirby'   => $nextMajor,
							'plugin'  => $plugin->name(),
							'version' => $compatibility['version'] ?? null
						]),
						'theme' => match ($status) {
							'update'  => 'info',
							'unknown' => 'passive',
							default   => 'negative'
						}
					];
				}

				$upgrade = [
					'headline' => I18n::template('system.upgrade', null, ['kirby' => $nextMajor]),
					'issues'   => $issues ?: [
						[
							'id'    => 'ready',
							'icon'  => 'check',
							'text'  => I18n::translate('system.upgrade.ready'),
							'theme' => 'positive'
						]
					],
					'link'     => 'https://getkirby.com/releases/' . $nextMajor
				];
			}

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
					'license' => $plugin->license()->toArray(),
					'name'    => [
						'text' => $plugin->name() ?? '–',
						'href' => $plugin->link(),
					],
					'status'  => $plugin->license()->status()->toArray(),
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

			if ($kirby->option('content.salt') === null) {
				$security[] = [
					'id'    => 'content-salt',
					'link'  => 'https://getkirby.com/security/content-salt',
					'text'  => I18n::translate('system.issues.content.salt'),
					'theme' => 'notice'
				];
			}

			if (($kirby->option('cookie.key') ?? Cookie::$key) === 'KirbyHttpCookieKey') {
				$security[] = [
					'id'    => 'cookie-key',
					'link'  => 'https://getkirby.com/security/cookie-key',
					'text'  => I18n::translate('system.issues.cookie.key'),
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
					'buttons'     => fn () =>
						ViewButtons::view('system')->render(),
					'environment' => $environment,
					'exceptions'  => $debugMode ? $exceptions : [],
					'info'        => $system->info(),
					'plugins'     => $plugins,
					'security'    => $security,
					'upgrade'     => $upgrade ?? null,
					'urls'        => $sensitive ?? []
				]
			];
		}
	],
];
