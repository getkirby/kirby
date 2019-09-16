<?php

namespace Kirby\Cms;

use Kirby\Session\Session;
use Kirby\Toolkit\Facade;

/**
 * Shortcut to the session object
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier GmbH
 * @license   https://getkirby.com/license
 */
class S extends Facade
{
    /**
     * @return \Kirby\Session\Session
     */
    public static function instance()
    {
        return App::instance()->session();
    }
}
