<?php

namespace Kirby\Cms;

use Kirby\Http\Visitor as HttpVisitor;
use Kirby\Toolkit\Facade;

/**
 * Shortcut to the visitor object
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class Visitor extends Facade
{
	public static function instance(): HttpVisitor
	{
		return App::instance()->visitor();
	}
}
