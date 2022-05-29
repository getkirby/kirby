<?php

namespace Kirby\Cms;

use Kirby\Filesystem\F;
use Kirby\Toolkit\A;

/**
 * The `Html` class provides methods for building
 * common HTML tags and also contains some helper
 * methods.
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class Html extends \Kirby\Toolkit\Html
{
    /**
     * Creates one or multiple CSS link tags
     * @since 3.7.0
     *
     * @param string|array $url Relative or absolute URLs, an array of URLs or `@auto` for automatic template css loading
     * @param string|array $options Pass an array of attributes for the link tag or a media attribute string
     * @return string|null
     */
    public static function css($url, $options = null): ?string
    {
        if (is_array($url) === true) {
            $links = A::map($url, fn ($url) => static::css($url, $options));
            return implode(PHP_EOL, $links);
        }

        if (is_string($options) === true) {
            $options = ['media' => $options];
        }

        $kirby = App::instance();

        if ($url === '@auto') {
            if (!$url = Url::toTemplateAsset('css/templates', 'css')) {
                return null;
            }
        }

        // only valid value for 'rel' is 'alternate stylesheet', if 'title' is given as well
        if (
            ($options['rel'] ?? '') !== 'alternate stylesheet' ||
            ($options['title'] ?? '') === ''
        ) {
            $options['rel'] = 'stylesheet';
        }

        $url  = ($kirby->component('css'))($kirby, $url, $options);
        $url  = Url::to($url);
        $attr = array_merge((array)$options, [
            'href' => $url
        ]);

        return '<link ' . static::attr($attr) . '>';
    }

    /**
     * Generates an `a` tag with an absolute Url
     *
     * @param string|null $href Relative or absolute Url
     * @param string|array|null $text If `null`, the link will be used as link text. If an array is passed, each element will be added unencoded
     * @param array $attr Additional attributes for the a tag.
     * @return string
     */
    public static function link(string $href = null, $text = null, array $attr = []): string
    {
        return parent::link(Url::to($href), $text, $attr);
    }

    /**
     * Creates a script tag to load a javascript file
     * @since 3.7.0
     *
     * @param string|array $url
     * @param string|array $options
     * @return string|null
     */
    public static function js($url, $options = null): ?string
    {
        if (is_array($url) === true) {
            $scripts = A::map($url, fn ($url) => static::js($url, $options));
            return implode(PHP_EOL, $scripts);
        }

        if (is_bool($options) === true) {
            $options = ['async' => $options];
        }

        $kirby = App::instance();

        if ($url === '@auto') {
            if (!$url = Url::toTemplateAsset('js/templates', 'js')) {
                return null;
            }
        }

        $url  = ($kirby->component('js'))($kirby, $url, $options);
        $url  = Url::to($url);
        $attr = array_merge((array)$options, ['src' => $url]);

        return '<script ' . static::attr($attr) . '></script>';
    }

    /**
     * Includes an SVG file by absolute or
     * relative file path.
     * @since 3.7.0
     *
     * @param string|\Kirby\Cms\File $file
     * @return string|false
     */
    public static function svg($file)
    {
        // support for Kirby's file objects
        if (
            is_a($file, 'Kirby\Cms\File') === true &&
            $file->extension() === 'svg'
        ) {
            return $file->read();
        }

        if (is_string($file) === false) {
            return false;
        }

        $extension = F::extension($file);

        // check for valid svg files
        if ($extension !== 'svg') {
            return false;
        }

        // try to convert relative paths to absolute
        if (file_exists($file) === false) {
            $root = App::instance()->root();
            $file = realpath($root . '/' . $file);
        }

        return F::read($file);
    }
}
