<?php

namespace Kirby\Toolkit;

use Exception;
use Kirby\Http\Url;

/**
 * HTML builder for the most common elements
 *
 * @package   Kirby Toolkit
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier GmbH
 * @license   https://opensource.org/licenses/MIT
 */
class Html extends Xml
{
    /**
     * An internal store for an HTML entities translation table
     *
     * @var array
     */
    public static $entities;

    /**
     * Closing string for void tags;
     * can be used to switch to trailing slashes if required
     *
     * ```php
     * Html::$void = ' />'
     * ```
     *
     * @var string
     */
    public static $void = '>';

    /**
     * List of HTML tags that are considered to be self-closing
     *
     * @var array
     */
    public static $voidList = [
        'area',
        'base',
        'br',
        'col',
        'command',
        'embed',
        'hr',
        'img',
        'input',
        'keygen',
        'link',
        'meta',
        'param',
        'source',
        'track',
        'wbr'
    ];

    /**
     * Generic HTML tag generator
     * Can be called like `Html::p('A paragraph', ['class' => 'text'])`
     *
     * @param string $tag Tag name
     * @param array $arguments Further arguments for the Html::tag() method
     * @return string
     */
    public static function __callStatic(string $tag, array $arguments = []): string
    {
        if (static::isVoid($tag) === true) {
            return static::tag($tag, null, ...$arguments);
        }

        return static::tag($tag, ...$arguments);
    }

    /**
     * Generates an `<a>` tag; automatically supports mailto: and tel: links
     *
     * @param string $href The URL for the `<a>` tag
     * @param string|array|null $text The optional text; if `null`, the URL will be used as text
     * @param array $attr Additional attributes for the tag
     * @return string The generated HTML
     */
    public static function a(string $href, $text = null, array $attr = []): string
    {
        if (Str::startsWith($href, 'mailto:')) {
            return static::email(substr($href, 7), $text, $attr);
        }

        if (Str::startsWith($href, 'tel:')) {
            return static::tel(substr($href, 4), $text, $attr);
        }

        return static::link($href, $text, $attr);
    }

    /**
     * Generates a single attribute or a list of attributes
     *
     * @param string|array $name String: A single attribute with that name will be generated.
     *                           Key-value array: A list of attributes will be generated. Don't pass a second argument in that case.
     * @param mixed $value If used with a `$name` string, pass the value of the attribute here.
     *                     If used with a `$name` array, this can be set to `false` to disable attribute sorting.
     * @return string|null The generated HTML attributes string
     */
    public static function attr($name, $value = null): ?string
    {
        // HTML supports boolean attributes without values
        if (is_array($name) === false && is_bool($value) === true) {
            return $value === true ? strtolower($name) : null;
        }

        // all other cases can share the XML variant
        $attr = parent::attr($name, $value);

        // HTML supports named entities
        $entities = parent::entities();
        $html = array_keys($entities);
        $xml  = array_values($entities);
        return str_replace($xml, $html, $attr);
    }

    /**
     * Converts lines in a string into HTML breaks
     *
     * @param string $string
     * @return string
     */
    public static function breaks(string $string): string
    {
        return nl2br($string);
    }

    /**
     * Generates an `<a>` tag with `mailto:`
     *
     * @param string $email The email address
     * @param string|array|null $text The optional text; if `null`, the email address will be used as text
     * @param array $attr Additional attributes for the tag
     * @return string The generated HTML
     */
    public static function email(string $email, $text = null, array $attr = []): string
    {
        if (empty($email) === true) {
            return '';
        }

        if (empty($text) === true) {
            // show only the email address without additional parameters
            $address = Str::contains($email, '?') ? Str::before($email, '?') : $email;

            $text = [Str::encode($address)];
        }

        $email = Str::encode($email);
        $attr  = array_merge([
            'href' => [
                'value'  => 'mailto:' . $email,
                'escape' => false
            ]
        ], $attr);

        // add rel=noopener to target blank links to improve security
        $attr['rel'] = static::rel($attr['rel'] ?? null, $attr['target'] ?? null);

        return static::tag('a', $text, $attr);
    }

    /**
     * Converts a string to an HTML-safe string
     *
     * @param string|null $string
     * @param bool $keepTags If true, existing tags won't be escaped
     * @return string The HTML string
     *
     * @psalm-suppress ParamNameMismatch
     */
    public static function encode(?string $string, bool $keepTags = false): string
    {
        if ($string === null) {
            return '';
        }

        if ($keepTags === true) {
            $list = static::entities();
            unset($list['"'], $list['<'], $list['>'], $list['&']);

            $search = array_keys($list);
            $values = array_values($list);

            return str_replace($search, $values, $string);
        }

        return htmlentities($string, ENT_COMPAT, 'utf-8');
    }

    /**
     * Returns the entity translation table
     *
     * @return array
     */
    public static function entities(): array
    {
        return self::$entities = self::$entities ?? get_html_translation_table(HTML_ENTITIES);
    }

    /**
     * Creates a `<figure>` tag with optional caption
     *
     * @param string|array $content Contents of the `<figure>` tag
     * @param string|array $caption Optional `<figcaption>` text to use
     * @param array $attr Additional attributes for the `<figure>` tag
     * @return string The generated HTML
     */
    public static function figure($content, $caption = '', array $attr = []): string
    {
        if ($caption) {
            $figcaption = static::tag('figcaption', $caption);

            if (is_string($content) === true) {
                $content = [static::encode($content, false)];
            }

            $content[] = $figcaption;
        }

        return static::tag('figure', $content, $attr);
    }

    /**
     * Embeds a GitHub Gist
     *
     * @param string $url Gist URL
     * @param string|null $file Optional specific file to embed
     * @param array $attr Additional attributes for the `<script>` tag
     * @return string The generated HTML
     */
    public static function gist(string $url, ?string $file = null, array $attr = []): string
    {
        if ($file === null) {
            $src = $url . '.js';
        } else {
            $src = $url . '.js?file=' . $file;
        }

        return static::tag('script', '', array_merge($attr, ['src' => $src]));
    }

    /**
     * Creates an `<iframe>`
     *
     * @param string $src
     * @param array $attr Additional attributes for the `<iframe>` tag
     * @return string The generated HTML
     */
    public static function iframe(string $src, array $attr = []): string
    {
        return static::tag('iframe', '', array_merge(['src' => $src], $attr));
    }

    /**
     * Generates an `<img>` tag
     *
     * @param string $src The URL of the image
     * @param array $attr Additional attributes for the `<img>` tag
     * @return string The generated HTML
     */
    public static function img(string $src, array $attr = []): string
    {
        $attr = array_merge([
            'src' => $src,
            'alt' => ' '
        ], $attr);

        return static::tag('img', '', $attr);
    }

    /**
     * Checks if a tag is self-closing
     *
     * @param string $tag
     * @return bool
     */
    public static function isVoid(string $tag): bool
    {
        return in_array(strtolower($tag), static::$voidList);
    }

    /**
     * Generates an `<a>` link tag (without automatic email: and tel: detection)
     *
     * @param string $href The URL for the `<a>` tag
     * @param string|array|null $text The optional text; if `null`, the URL will be used as text
     * @param array $attr Additional attributes for the tag
     * @return string The generated HTML
     */
    public static function link(string $href, $text = null, array $attr = []): string
    {
        $attr = array_merge(['href' => $href], $attr);

        if (empty($text) === true) {
            $text = $attr['href'];
        }

        if (is_string($text) === true && Str::isUrl($text) === true) {
            $text = Url::short($text);
        }

        // add rel=noopener to target blank links to improve security
        $attr['rel'] = static::rel($attr['rel'] ?? null, $attr['target'] ?? null);

        return static::tag('a', $text, $attr);
    }

    /**
     * Add noopener & noreferrer to rels when target is `_blank`
     *
     * @param string|null $rel Current `rel` value
     * @param string|null $target Current `target` value
     * @return string|null New `rel` value or `null` if not needed
     */
    public static function rel(?string $rel = null, ?string $target = null): ?string
    {
        $rel = trim($rel);

        if ($target === '_blank') {
            if (empty($rel) === false) {
                return $rel;
            }

            return trim($rel . ' noopener noreferrer', ' ');
        }

        return $rel;
    }

    /**
     * Builds an HTML tag
     *
     * @param string $name Tag name
     * @param array|string $content Scalar value or array with multiple lines of content; self-closing
     *                              tags are generated automatically based on the `Html::isVoid()` list
     * @param array $attr An associative array with additional attributes for the tag
     * @param string|null $indent Indentation string, defaults to two spaces or `null` for output on one line
     * @param int $level Indentation level
     * @return string The generated HTML
     */
    public static function tag(string $name, $content = '', array $attr = null, string $indent = null, int $level = 0): string
    {
        // treat an explicit `null` value as an empty tag
        // as void tags are already covered below
        if ($content === null) {
            $content = '';
        }
        
        // force void elements to be self-closing
        if (static::isVoid($name) === true) {
            $content = null;
        }

        return parent::tag($name, $content, $attr, $indent, $level);
    }

    /**
     * Generates an `<a>` tag for a phone number
     *
     * @param string $tel The phone number
     * @param string|array|null $text The optional text; if `null`, the phone number will be used as text
     * @param array $attr Additional attributes for the tag
     * @return string The generated HTML
     */
    public static function tel(string $tel, $text = null, array $attr = []): string
    {
        $number = preg_replace('![^0-9\+]+!', '', $tel);

        if (empty($text) === true) {
            $text = $tel;
        }

        return static::link('tel:' . $number, $text, $attr);
    }

    /**
     * Properly encodes tag contents
     *
     * @param mixed $value
     * @return string|null
     */
    public static function value($value): ?string
    {
        if ($value === true) {
            return 'true';
        }

        if ($value === false) {
            return 'false';
        }

        if (is_numeric($value) === true) {
            return (string)$value;
        }

        if ($value === null || $value === '') {
            return null;
        }

        return static::encode($value, false);
    }

    /**
     * Creates a video embed via `<iframe>` for YouTube or Vimeo
     * videos; the embed URLs are automatically detected from
     * the given URL
     *
     * @param string $url Video URL
     * @param array $options Additional `vimeo` and `youtube` options
     *                       (will be used as query params in the embed URL)
     * @param array $attr Additional attributes for the `<iframe>` tag
     * @return string The generated HTML
     */
    public static function video(string $url, array $options = [], array $attr = []): string
    {
        // YouTube video
        if (preg_match('!youtu!i', $url) === 1) {
            return static::youtube($url, $options['youtube'] ?? [], $attr);
        }

        // Vimeo video
        if (preg_match('!vimeo!i', $url) === 1) {
            return static::vimeo($url, $options['vimeo'] ?? [], $attr);
        }

        throw new Exception('Unexpected video type');
    }

    /**
     * Embeds a Vimeo video by URL in an `<iframe>`
     *
     * @param string $url Vimeo video URL
     * @param array $options Query params for the embed URL
     * @param array $attr Additional attributes for the `<iframe>` tag
     * @return string The generated HTML
     */
    public static function vimeo(string $url, array $options = [], array $attr = []): string
    {
        if (preg_match('!vimeo.com\/([0-9]+)!i', $url, $array) === 1) {
            $id = $array[1];
        } elseif (preg_match('!player.vimeo.com\/video\/([0-9]+)!i', $url, $array) === 1) {
            $id = $array[1];
        } else {
            throw new Exception('Invalid Vimeo source');
        }

        // build the options query
        if (empty($options) === false) {
            $query = '?' . http_build_query($options);
        } else {
            $query = '';
        }

        $url = 'https://player.vimeo.com/video/' . $id . $query;

        return static::iframe($url, array_merge(['allowfullscreen' => true], $attr));
    }

    /**
     * Embeds a YouTube video by URL in an `<iframe>`
     *
     * @param string $url YouTube video URL
     * @param array $options Query params for the embed URL
     * @param array $attr Additional attributes for the `<iframe>` tag
     * @return string The generated HTML
     */
    public static function youtube(string $url, array $options = [], array $attr = []): string
    {
        // default YouTube embed domain
        $domain     = 'youtube.com';
        $uri        = 'embed/';
        $id         = null;
        $urlOptions = [];

        $schemes = [
            // https://www.youtube.com/embed/videoseries?list=PLj8e95eaxiB9goOAvINIy4Vt3mlWQJxys
            [
                'pattern' => 'youtube.com\/embed\/videoseries\?list=([a-zA-Z0-9_-]+)',
                'uri'     => 'embed/videoseries?list='
            ],

            // https://www.youtube-nocookie.com/embed/videoseries?list=PLj8e95eaxiB9goOAvINIy4Vt3mlWQJxys
            [
                'pattern' => 'youtube-nocookie.com\/embed\/videoseries\?list=([a-zA-Z0-9_-]+)',
                'domain'  => 'www.youtube-nocookie.com',
                'uri'     => 'embed/videoseries?list='
            ],

            // https://www.youtube.com/embed/d9NF2edxy-M
            // https://www.youtube.com/embed/d9NF2edxy-M?start=10
            ['pattern' => 'youtube.com\/embed\/([a-zA-Z0-9_-]+)(?:\?(.+))?'],

            // https://www.youtube-nocookie.com/embed/d9NF2edxy-M
            // https://www.youtube-nocookie.com/embed/d9NF2edxy-M?start=10
            [
                'pattern' => 'youtube-nocookie.com\/embed\/([a-zA-Z0-9_-]+)(?:\?(.+))?',
                'domain'  => 'www.youtube-nocookie.com'
            ],

            // https://www.youtube-nocookie.com/watch?v=d9NF2edxy-M
            // https://www.youtube-nocookie.com/watch?v=d9NF2edxy-M&t=10
            [
                'pattern' => 'youtube-nocookie.com\/watch\?v=([a-zA-Z0-9_-]+)(?:&(.+))?',
                'domain'  => 'www.youtube-nocookie.com'
            ],

            // https://www.youtube-nocookie.com/playlist?list=PLj8e95eaxiB9goOAvINIy4Vt3mlWQJxys
            [
                'pattern' => 'youtube-nocookie.com\/playlist\?list=([a-zA-Z0-9_-]+)',
                'domain'  => 'www.youtube-nocookie.com',
                'uri'     => 'embed/videoseries?list='
            ],

            // https://www.youtube.com/watch?v=d9NF2edxy-M
            // https://www.youtube.com/watch?v=d9NF2edxy-M&t=10
            ['pattern' => 'youtube.com\/watch\?v=([a-zA-Z0-9_-]+)(?:&(.+))?'],

            // https://www.youtube.com/playlist?list=PLj8e95eaxiB9goOAvINIy4Vt3mlWQJxys
            [
                'pattern' => 'youtube.com\/playlist\?list=([a-zA-Z0-9_-]+)',
                'uri'     => 'embed/videoseries?list='
            ],

            // https://youtu.be/d9NF2edxy-M
            // https://youtu.be/d9NF2edxy-M?t=10
            ['pattern' => 'youtu.be\/([a-zA-Z0-9_-]+)(?:\?(.+))?']
        ];

        foreach ($schemes as $schema) {
            if (preg_match('!' . $schema['pattern'] . '!i', $url, $array) === 1) {
                $domain = $schema['domain'] ?? $domain;
                $uri    = $schema['uri'] ?? $uri;
                $id     = $array[1];
                if (isset($array[2]) === true) {
                    parse_str($array[2], $urlOptions);

                    // convert video URL options to embed URL options
                    if (isset($urlOptions['t']) === true) {
                        $urlOptions['start'] = $urlOptions['t'];
                        unset($urlOptions['t']);
                    }
                }
                break;
            }
        }

        // no match
        if ($id === null) {
            throw new Exception('Invalid YouTube source');
        }

        // build the options query
        if (empty($options) === false || empty($urlOptions) === false) {
            $query = (Str::contains($uri, '?') === true ? '&' : '?') . http_build_query(array_merge($urlOptions, $options));
        } else {
            $query = '';
        }

        $url = 'https://' . $domain . '/' . $uri . $id . $query;

        return static::iframe($url, array_merge(['allowfullscreen' => true], $attr));
    }
}
