<?php

namespace Kirby\Panel\Controller\Dialog;

use Kirby\Cms\Find;
use Kirby\Cms\Site;
use Kirby\Panel\Controller\DialogController;

/**
 * Controls a Panel dialog for the site
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 * @unstable
 */
abstract class SiteDialogController extends DialogController
{
	protected Site $site;

	public function __construct()
	{
		parent::__construct();

		$this->site = Find::site();
	}
}
