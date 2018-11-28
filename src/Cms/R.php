<?php

namespace Kirby\Cms;

use Kirby\Toolkit\Facade;

/**
 * Shortcut to the request object
 */
class R extends Facade
{
    /**
     * @return Kirby\Http\Request
     */
    protected static function instance()
    {
        return App::instance()->request();
    }
}
