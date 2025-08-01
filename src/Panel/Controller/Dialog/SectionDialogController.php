<?php

namespace Kirby\Panel\Controller\Dialog;

use Kirby\Panel\Controller\DialogController;
use Kirby\Panel\Controller\SectionController;
use Kirby\Panel\Routes\DialogRoutes;

/**
 * @package   Kirby Panel
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 * @unstable
 */
class SectionDialogController extends DialogController
{
	use SectionController;

	public function routes(): array
	{
		$routes = new class ($this->area(), $this->section->dialogs()) extends DialogRoutes {
			// ensure section dialog routes are not prefixed again with /dialogs
			protected static string $prefix = '';
		};

		return $routes->toArray();
	}
}
