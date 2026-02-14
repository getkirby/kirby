<?php

namespace Kirby\Panel\Controller\View;

use Kirby\Panel\Controller\ViewController;
use Kirby\Panel\Lab\Docs;
use Kirby\Panel\Ui\View;

/**
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
class LabDocsViewController extends ViewController
{
	public function breadcrumb(): array
	{
		return [
			[
				'label' => 'Docs',
				'link'  => 'lab/docs'
			]
		];
	}

	public function info(): string|null
	{
		// @codeCoverageIgnoreStart
		if (Docs::isInstalled() === false) {
			return 'The UI docs are not installed.';
		}
		// @codeCoverageIgnoreEnd

		return null;
	}

	public function load(): View
	{
		return new View(
			component: 'k-lab-index-view',
			breadcrumb: $this->breadcrumb(),
			categories: [['examples' => Docs::all()]],
			info: $this->info(),
			tab: 'docs',
			title: 'Docs',
		);
	}
}
