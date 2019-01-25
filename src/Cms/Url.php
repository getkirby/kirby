<?php

namespace Kirby\Cms;

use Kirby\Http\Url as BaseUrl;
use Kirby\Toolkit\Str;

/**
 * Extension of the Kirby\Http\Url class
 * with a specific Url::home method that always
 * creates the correct base Url and a template asset
 * Url builder.
 */
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
        return App::instance()->url();
    }

    /**
     * Creates an absolute Url to a template asset if it exists. This is used in the `css()` and `js()` helpers
     *
     * @param string $assetPath
     * @param string $extension
     * @return string|null
     */
    public static function toTemplateAsset(string $assetPath, string $extension)
    {
        $kirby = App::instance();
        $page  = $kirby->site()->page();
        $path  = $assetPath . '/' . $page->template() . '.' . $extension;
        $file  = $kirby->root('assets') . '/' . $path;
        $url   = $kirby->url('assets') . '/' . $path;

        return file_exists($file) === true ? $url : null;
    }

    /**
     * Smart resolver for internal and external urls
     *
     * @param string $path
     * @param array|string|null $options Either an array of options for the Uri class or a language string
     * @return string
     */
    public static function to(string $path = null, $options = null): string
    {
        $kirby    = App::instance();
        $language = null;

        // get language from simple string option
        if (is_string($options) === true) {
            $language = $options;
            $options  = null;
        }

        // get language from array
        if (is_array($options) === true && isset($options['language']) === true) {
            $language = $options['language'];
            unset($options['language']);
        }

        // get a language url for the linked page, if the page can be found
        if ($language !== null && $kirby->multilang() === true && $page = page($path)) {
            $path = $page->url($language);
        }

        if ($handler = $kirby->component('url')) {
            return $handler($kirby, $path, $options, function (string $path = null, $options = null) {
                return parent::to($path, $options);
            });
        }

        return parent::to($path, $options);
    }
}
