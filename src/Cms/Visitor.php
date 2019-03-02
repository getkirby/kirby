<?php

namespace Kirby\Cms;

use Kirby\Toolkit\Facade;

/**
 * Shortcut to the visitor object
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
