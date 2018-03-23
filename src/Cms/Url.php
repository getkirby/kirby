<?php

namespace Kirby\Cms;

use Kirby\Toolkit\Url as BaseUrl;
use Kirby\Util\Str;

class Url extends BaseUrl
{

    /**
     * Returns the Url to the homepage
     *
     * @return string
     */
    public static function home(): string
    {
        return App::instance()->url();
    }

    /**
     * Smart resolver for internal and external urls
     *
     * @param string $path
     * @return string
     */
    public static function to(string $path = null): string
    {
        $kirby = App::instance();
        $path  = trim($path);

        if ($path === '' || $path === '/') {
            return $kirby->url();
        }

        if (static::isAbsolute($path) === true) {
            return $path;
        }

        if (Str::startsWith($path, '#') === true) {
            return $path;
        }

        return $kirby->url() . '/' . rtrim($path, '/');
    }

}
