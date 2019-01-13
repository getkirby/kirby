<?php

namespace Kirby\Cms;

use Kirby\Session\Session;
use Kirby\Toolkit\Facade;

/**
 * Shortcut to the session object
 */
class S extends Facade
{
    /**
     * @return Session
     */
    protected static function instance()
    {
        return App::instance()->session();
    }
}
