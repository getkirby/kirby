<?php

namespace Kirby\Panel\Controller\View;

use Kirby\Panel\Controller\ViewController;
use Kirby\Panel\Lab\Category;
use Kirby\Panel\Ui\View;

/**
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
class LabExamplesViewController extends ViewController
{
	public function info(): string|null
	{
		// @codeCoverageIgnoreStart
		if (Category::isInstalled() === false) {
			return 'The default Lab examples are not installed.';
		}
		// @codeCoverageIgnoreEnd

		return null;
	}

	public function load(): View
	{
		return new View(
			component: 'k-lab-index-view',
			categories: Category::all(),
			info: $this->info(),
			tab: 'examples',
		);
	}
}
