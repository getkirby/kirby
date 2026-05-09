<?php

namespace Kirby\Cms;

use Kirby\Session\Session;
use Kirby\Toolkit\Facade;

/**
 * Shortcut to the session object
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class S extends Facade
{
	public static function instance(): Session
	{
		return App::instance()->session();
	}
}
