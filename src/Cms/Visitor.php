<?php

namespace Kirby\Cms;

use Kirby\Toolkit\Facade;

/**
 * Shortcut to the visitor object
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier GmbH
 * @license   https://getkirby.com/license
 */
class Visitor extends Facade
{
    /**
     * @return \Kirby\Http\Visitor
     */
    public static function instance()
    {
        return App::instance()->visitor();
    }
}
