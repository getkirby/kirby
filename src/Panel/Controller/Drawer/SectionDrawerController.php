<?php

namespace Kirby\Panel\Controller\Drawer;

use Kirby\Panel\Controller\DrawerController;
use Kirby\Panel\Controller\SectionController;
use Kirby\Panel\Routes\DrawerRoutes;

/**
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 *
 * @unstable
 */
class SectionDrawerController extends DrawerController
{
	use SectionController;

	public function routes(): array
	{
		$routes = new class ($this->area(), $this->section->drawers()) extends DrawerRoutes {
			// ensure section drawer routes are not prefixed again with /drawers
			protected static string $prefix = '';
		};

		return $routes->toArray();
	}
}
