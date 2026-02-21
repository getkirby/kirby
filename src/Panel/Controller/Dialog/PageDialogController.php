<?php

namespace Kirby\Panel\Controller\Dialog;

use Kirby\Cms\Find;
use Kirby\Cms\Page;
use Kirby\Panel\Controller\DialogController;

/**
 * Controls a Panel dialog for a specific page
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 *
 * @unstable
 */
abstract class PageDialogController extends DialogController
{
	public function __construct(
		public Page $page
	) {
		parent::__construct();
	}

	public static function factory(string $id): static
	{
		return new static(page: Find::page($id));
	}
}
