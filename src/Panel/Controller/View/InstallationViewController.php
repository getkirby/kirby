<?php

namespace Kirby\Panel\Controller\View;

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
}
