<?php

namespace Kirby\Cms;

use Kirby\Session\Session;
use Kirby\Toolkit\Facade;
use Override;

/**
 * Shortcut to the session object
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class S extends Facade
{
	#[Override]
	public static function instance(): Session
	{
		return App::instance()->session();
	}
}
