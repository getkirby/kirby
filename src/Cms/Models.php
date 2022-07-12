<?php

namespace Kirby\Cms;

/**
 * Foundation for Pages, Files and Users collections.
 *
 * @package   Kirby Cms
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
abstract class Models extends Collection
{
	/**
	 * All registered custom methods
	 *
	 * @var array
	 */
	public static $methods = [];
}
