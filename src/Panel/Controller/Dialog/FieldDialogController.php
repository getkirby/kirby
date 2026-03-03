<?php

namespace Kirby\Panel\Controller\Dialog;

use Kirby\Panel\Controller\DialogController;
use Kirby\Panel\Controller\FieldController;
use Kirby\Panel\Routes\DialogRoutes;

/**
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 *
 * @unstable
 */
class FieldDialogController extends DialogController
{
	use FieldController;

	public function routes(): array
	{
		$routes = new class ($this->area(), $this->field->dialogs()) extends DialogRoutes {
			// ensure field dialog routes are not prefixed again with /dialogs
			protected static string $prefix = '';
		};

		return $routes->toArray();
	}
}
