<?php

namespace Kirby\Cms;

use Kirby\Http\Url as BaseUrl;
use Kirby\Toolkit\Str;

class Url extends BaseUrl
{

    public static $home = null;

    /**
     * Returns the Url to the homepage
     *
     * @return string
     */
    public static function home(): string
    {
        return static::$home = static::$home ?? App::instance()->url();
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
