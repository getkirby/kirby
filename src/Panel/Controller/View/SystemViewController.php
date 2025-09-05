<?php

namespace Kirby\Panel\Controller\View;

use Kirby\Cms\License;
use Kirby\Cms\System;
use Kirby\Cms\System\UpdateStatus;
use Kirby\Panel\Controller\ViewController;
use Kirby\Panel\Ui\Button\ViewButtons;
use Kirby\Panel\Ui\Stat;
use Kirby\Panel\Ui\Stats;
use Kirby\Panel\Ui\View;
use Kirby\Plugin\Plugin;

/**
 * Controls the system view
 *
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
class SystemViewController extends ViewController
{
	protected array $exceptions;
	protected License $license;
	protected array $plugins;
	protected System $system;
	protected UpdateStatus|null $update;

	public function __construct()
	{
		parent::__construct();

		$this->system     = $this->kirby->system();
		$this->license    = $this->system->license();
		$this->update     = $this->system->updateStatus();
		$this->exceptions = $this->update?->exceptionMessages() ?? [];
	}

	public function buttons(): ViewButtons
	{
		return ViewButtons::view('system');
	}

	public function exceptions(): array
	{
		if ($this->isDebugging() === false) {
			return [];
		}

		// Call `::plugins()` to ensure they inject their exceptions
		$this->plugins();

		return $this->exceptions;
	}

	protected function isDebugging(): bool
	{
		return $this->kirby->option('debug', false) === true;
	}

	protected function isLocal(): bool
	{
		return $this->system->isLocal();
	}

	public function load(): View
	{
		return new View(
			component: 'k-system-view',
			buttons: $this->buttons(),
			environment: $this->stats()->reports(),
			exceptions: $this->exceptions(),
			info: $this->system->info(),
			plugins: $this->plugins(),
			security: $this->security(),
			urls: $this->urls()
		);
	}

	public function plugins(): array
	{
		return $this->plugins ??= $this->system->plugins()->values(function (Plugin $plugin) {
			$authors = $plugin->authorsNames();
			$update  = $plugin->updateStatus();
			$version = $update?->toArray() ?? $plugin->version() ?? '–';

			// Inject exceptions from plugin to global exceptions
			$this->exceptions = [
				...$this->exceptions,
				...$update?->exceptionMessages() ?? []
			];

			return [
				'author'  => $authors === '' ? '–' : $authors,
				'license' => $plugin->license()->toArray(),
				'name'    => [
					'text' => $plugin->name() ?? '–',
					'href' => $plugin->link()
				],
				'status'  => $plugin->license()->status()->toArray(),
				'version' => $version,
			];
		});
	}

	public function security(): array
	{
		$security = $this->update?->messages() ?? [];

		if ($this->isLocal() === true) {
			$security[] = [
				'id'    => 'local',
				'icon'  => 'info',
				'theme' => 'info',
				'text'  => $this->i18n('system.issues.local')
			];
		}

		if ($this->isDebugging() === true) {
			$security[] = [
				'id'    => 'debug',
				'icon'  => $this->isLocal() ? 'info' : 'alert',
				'theme' => $this->isLocal() ? 'info' : 'negative',
				'text'  => $this->i18n('system.issues.debug'),
				'link'  => 'https://getkirby.com/security/debug'
			];
		}

		if (
			$this->isLocal() === false &&
			$this->kirby->environment()->https() !== true
		) {
			$security[] = [
				'id'   => 'https',
				'text' => $this->i18n('system.issues.https'),
				'link' => 'https://getkirby.com/security/https'
			];
		}

		if ($this->kirby->option('panel.vue.compiler', null) === null) {
			$security[] = [
				'id'    => 'vue-compiler',
				'link'  => 'https://getkirby.com/security/vue-compiler',
				'text'  => $this->i18n('system.issues.vue.compiler'),
				'theme' => 'notice'
			];
		}

		return $security;
	}

	public function stats(): Stats
	{
		return new Stats(reports: [
			new Stat(
				label: $this->license->status()->label(),
				value: $this->license->label(),
				theme: $this->license->status()->theme(),
				icon: $this->license->status()->icon(),
				dialog: $this->license->status()->dialog()
			),
			new Stat(
				label: $this->update?->label() ?? $this->i18n('version'),
				value: $this->kirby->version(),
				link: $this->update?->url() ??
					'https://github.com/getkirby/kirby/releases/tag/' . $this->kirby->version(),
				theme: $this->update?->theme(),
				icon: $this->update?->icon() ?? 'info'
			),
			new Stat(
				label: 'PHP',
				value: phpversion(),
				icon: 'code'
			),
			new Stat(
				label: $this->i18n('server'),
				value: $this->system->serverSoftwareShort() ?? '?',
				icon: 'server'
			)
		]);
	}

	public function urls(): array
	{
		if ($this->isLocal() === true) {
			return [];
		}

		// sensitive URLs
		return [
			'content' => $this->system->exposedFileUrl('content'),
			'git'     => $this->system->exposedFileUrl('git'),
			'kirby'   => $this->system->exposedFileUrl('kirby'),
			'site'    => $this->system->exposedFileUrl('site')
		];
	}
}
