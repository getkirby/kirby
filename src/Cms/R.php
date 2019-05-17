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
 * @copyright Bastian Allgeier GmbH
 * @license   https://getkirby.com/license
 */
class R extends Facade
{
    /**
     * @return Kirby\Cms\Request
     */
    public static function instance()
    {
        return App::instance()->request();
    }
}
