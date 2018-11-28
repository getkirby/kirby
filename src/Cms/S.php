<?php

namespace Kirby\Cms;

use Kirby\Toolkit\Facade;

/**
 * Shortcut to the session object
 */
class S extends Facade
{
    /**
     * @return Kirby\Session\Session
     */
    protected static function instance()
    {
        return App::instance()->session();
    }
}
