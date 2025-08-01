<?php

namespace Kirby\Panel\Controller\Drawer;

use Kirby\Panel\Controller\DrawerController;
use Kirby\Panel\Controller\FieldController;
use Kirby\Panel\Routes\DrawerRoutes;

/**
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 * @unstable
 */
class FieldDrawerController extends DrawerController
{
	use FieldController;

	public function routes(): array
	{
		$routes = new class ($this->area(), $this->field->drawers()) extends DrawerRoutes {
			// ensure field drawer routes are not prefixed again with /drawers
			protected static string $prefix = '';
		};

		return $routes->toArray();
	}
}
