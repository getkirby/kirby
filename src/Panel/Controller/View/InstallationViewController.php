<?php

namespace Kirby\Panel\Controller\View;

use Kirby\Exception\Exception;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Cms\System;
use Kirby\Panel\Controller\ViewController;
use Kirby\Panel\Ui\View;

/**
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
class InstallationViewController extends ViewController
{
	protected System $system;

	public function __construct()
	{
		parent::__construct();
		$this->system = $this->kirby->system();
	}

	public function load(): View
	{
		return new View(
			component:     'k-installation-view',
			isInstallable: $this->system->isInstallable(),
			isInstalled:   $this->system->isInstalled(),
			isOk:          $this->system->isOk(),
			requirements:  [
				...$this->system->status(),
				'extensions' => $this->system->extensions()
			],
			translations:  $this->translations()
		);
	}

	public function translations(): array
	{
		return $this->kirby->translations()->values(fn ($translation) => [
			'text'  => $translation->name(),
			'value' => $translation->code(),
		]);
	}

	public function submit(): array
	{
		$auth = $this->kirby->auth();

		// csrf token check
		if ($auth->type() === 'session' && $auth->csrf() === false) {
			throw new InvalidArgumentException(
				message: 'Invalid CSRF token'
			);
		}

		if ($this->system->isOk() === false) {
			throw new Exception(
				message: 'The server is not setup correctly'
			);
		}

		if ($this->system->isInstallable() === false) {
			throw new Exception(
				message: 'The Panel cannot be installed'
			);
		}

		if ($this->system->isInstalled() === true) {
			throw new Exception(
				message: 'The Panel is already installed'
			);
		}

		$data = $this->request->body()->data();

		// create the first user
		$user = $this->kirby->users()->create($data);
		$user->login($data['password'] ?? null);

		return [
			...$this->load()->render(),
			'message' => $this->i18n('welcome') . '!',
			'reload'  => [
				'globals' => ['system', 'translation']
			]
		];
	}
}
