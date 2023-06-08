<?php

namespace Kirby\Cms;

use Kirby\Http\Request;
use Kirby\Toolkit\Facade;

/**
 * Shortcut to the request object
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class R extends Facade
{
	public static function instance(): Request
	{
		return App::instance()->request();
	}
}
