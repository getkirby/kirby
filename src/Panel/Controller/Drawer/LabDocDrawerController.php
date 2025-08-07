<?php

namespace Kirby\Panel\Controller\Drawer;

use Kirby\Panel\Controller\DrawerController;
use Kirby\Panel\Lab\Doc;
use Kirby\Panel\Lab\Docs;
use Kirby\Panel\Ui\Drawer;
use Kirby\Panel\Ui\Drawer\TextDrawer;

/**
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 * @unstable
 */
class LabDocDrawerController extends DrawerController
{
	protected Doc|null $doc = null;

	public function __construct(
		protected string $component
	) {
		if (Docs::isInstalled() === true) {
			$this->doc = Doc::factory($this->component);
		}
	}

	public function load(): Drawer
	{
		// @codeCoverageIgnoreStart
		if ($this->doc === null) {
			return new TextDrawer(
				text: 'The UI docs are not installed.'
			);
		}
		// @codeCoverageIgnoreEnd

		return new Drawer(
			component: 'k-lab-docs-drawer',
			icon: 'book',
			title: $this->component,
			docs:  $this->doc->toArray()
		);
	}
}
