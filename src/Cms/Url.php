<?php

namespace Kirby\Cms;

use Kirby\Toolkit\Str;
use Kirby\Toolkit\Url as BaseUrl;

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

        return $kirby->url() . '/' . trim($path, '/');
    }

    public static function toTemplateAsset(string $assetPath, string $extension)
    {
        $kirby = App::instance();
        $page  = $kirby->site()->page();
        $path  = $assetPath . '/' . $page->template() . '.' . $extension;
        $file  = $kirby->root('assets') . '/' . $path;
        $url   = $kirby->url('assets') . '/' . $path;

        return file_exists($file) === true ? $url : null;
    }

}
