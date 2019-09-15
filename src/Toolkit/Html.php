<?php

namespace Kirby\Toolkit;

use Exception;
use Kirby\Http\Url;

/**
 * Html builder for the most common elements
 *
 * @package   Kirby Toolkit
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier GmbH
 * @license   https://opensource.org/licenses/MIT
 */
class Html
{
    /**
     * An internal store for a html entities translation table
     *
     * @var array
     */
    public static $entities;

    /**
     * Can be used to switch to trailing slashes if required
     *
     * ```php
     * html::$void = ' />'
     * ```
     *
     * @var string $void
     */
    public static $void = '>';

    /**
     * Generic HTML tag generator
     *
     * @param string $tag
     * @param array $arguments
     * @return string
     */
    public static function __callStatic(string $tag, array $arguments = []): string
    {
        if (static::isVoid($tag) === true) {
            return Html::tag($tag, null, ...$arguments);
        }

        return Html::tag($tag, ...$arguments);
    }

    /**
     * Generates an `a` tag
     *
     * @param string $href The url for the `a` tag
     * @param mixed $text The optional text. If `null`, the url will be used as text
     * @param array $attr Additional attributes for the tag
     * @return string the generated html
     */
    public static function a(string $href = null, $text = null, array $attr = []): string
    {
        if (Str::startsWith($href, 'mailto:')) {
            return static::email($href, $text, $attr);
        }

        if (Str::startsWith($href, 'tel:')) {
            return static::tel($href, $text, $attr);
        }

        return static::link($href, $text, $attr);
    }

    /**
     * Generates a single attribute or a list of attributes
     *
     * @param string $name mixed string: a single attribute with that name will be generated. array: a list of attributes will be generated. Don't pass a second argument in that case.
     * @param string $value if used for a single attribute, pass the content for the attribute here
     * @return string the generated html
     */
    public static function attr($name, $value = null): string
    {
        if (is_array($name) === true) {
            $attributes = [];

            ksort($name);

            foreach ($name as $key => $val) {
                $a = static::attr($key, $val);

                if ($a) {
                    $attributes[] = $a;
                }
            }

            return implode(' ', $attributes);
        }

        if ($value === null || $value === '' || $value === []) {
            return false;
        }

        if ($value === ' ') {
            return strtolower($name) . '=""';
        }

        if (is_bool($value) === true) {
            return $value === true ? strtolower($name) : '';
        }

        if (is_array($value) === true) {
            if (isset($value['value'], $value['escape'])) {
                $value = $value['escape'] === true ? htmlspecialchars($value['value'], ENT_QUOTES, 'UTF-8') : $value['value'];
            } else {
                $value = implode(' ', array_filter($value, function ($value) {
                    return !empty($value) || is_numeric($value);
                }));
            }
        } else {
            $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
        }

        return strtolower($name) . '="' . $value . '"';
    }

    /**
     * Converts lines in a string into html breaks
     *
     * @param string $string
     * @return string
     */
    public static function breaks(string $string = null): string
    {
        return nl2br($string);
    }

    /**
     * Removes all html tags and encoded chars from a string
     *
     * <code>
     *
     * echo html::decode('some uber <em>crazy</em> stuff');
     * // output: some uber crazy stuff
     *
     * </code>
     *
     * @param  string  $string
     * @return string  The html string
     */
    public static function decode(string $string = null): string
    {
        $string = strip_tags($string);
        return html_entity_decode($string, ENT_COMPAT, 'utf-8');
    }

    /**
     * Generates an `a` tag with `mailto:`
     *
     * @param string $email The url for the a tag
     * @param mixed $text The optional text. If null, the url will be used as text
     * @param array $attr Additional attributes for the tag
     * @return string the generated html
     */
    public static function email(string $email, $text = null, array $attr = []): string
    {
        if (empty($email) === true) {
            return '';
        }

        if (empty($text) === true) {
            // show only the eMail address without additional parameters (if the 'text' argument is empty)
            $text = [Str::encode(Str::split($email, '?')[0])];
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
     * Converts a string to a html-safe string
     *
     * @param  string  $string
     * @param  bool    $keepTags
     * @return string  The html string
     */
    public static function encode(string $string = null, bool $keepTags = false): string
    {
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
     * Returns the entities translation table
     *
     * @return array
     */
    public static function entities(): array
    {
        return static::$entities = static::$entities ?? get_html_translation_table(HTML_ENTITIES);
    }

    /**
     * Creates a figure tag with optional caption
     *
     * @param string|array $content
     * @param string|array $caption
     * @param array $attr
     * @return string
     */
    public static function figure($content, $caption = null, array $attr = []): string
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
     * Embeds a gist
     *
     * @param string $url
     * @param string $file
     * @param array $attr
     * @return string
     */
    public static function gist(string $url, string $file = null, array $attr = []): string
    {
        if ($file === null) {
            $src = $url . '.js';
        } else {
            $src = $url . '.js?file=' . $file;
        }

        return static::tag('script', null, array_merge($attr, [
            'src' => $src
        ]));
    }

    /**
     * Creates an iframe
     *
     * @param string $src
     * @param array $attr
     * @return string
     */
    public static function iframe(string $src, array $attr = []): string
    {
        return static::tag('iframe', null, array_merge(['src' => $src], $attr));
    }

    /**
     * Generates an img tag
     *
     * @param string $src The url of the image
     * @param array $attr Additional attributes for the image tag
     * @return string the generated html
     */
    public static function img(string $src, array $attr = []): string
    {
        $attr = array_merge([
            'src' => $src,
            'alt' => ' '
        ], $attr);

        return static::tag('img', null, $attr);
    }

    /**
     * Checks if a tag is self-closing
     *
     * @param string $tag
     * @return bool
     */
    public static function isVoid(string $tag): bool
    {
        $void = [
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
            'wbr',
        ];

        return in_array(strtolower($tag), $void);
    }

    /**
     * Generates an `a` link tag
     *
     * @param string $href The url for the `a` tag
     * @param mixed $text The optional text. If `null`, the url will be used as text
     * @param array $attr Additional attributes for the tag
     * @return string the generated html
     */
    public static function link(string $href = null, $text = null, array $attr = []): string
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
     * Add noopeener noreferrer to rels when target is `_blank`
     *
     * @param string $rel
     * @param string $target
     * @return string|null
     */
    public static function rel(string $rel = null, string $target = null)
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
     * Generates an Html tag with optional content and attributes
     *
     * @param string $name The name of the tag, i.e. `a`
     * @param mixed $content The content if availble. Pass `null` to generate a self-closing tag, Pass an empty string to generate empty content
     * @param array $attr An associative array with additional attributes for the tag
     * @return string The generated Html
     */
    public static function tag(string $name, $content = null, array $attr = []): string
    {
        $html = '<' . $name;
        $attr = static::attr($attr);

        if (empty($attr) === false) {
            $html .= ' ' . $attr;
        }

        if (static::isVoid($name) === true) {
            $html .= static::$void;
        } else {
            if (is_array($content) === true) {
                $content = implode($content);
            } else {
                $content = static::encode($content, false);
            }

            $html .= '>' . $content . '</' . $name . '>';
        }

        return $html;
    }


    /**
     * Generates an `a` tag for a phone number
     *
     * @param string $tel The phone number
     * @param mixed $text The optional text. If `null`, the number will be used as text
     * @param array $attr Additional attributes for the tag
     * @return string the generated html
     */
    public static function tel($tel = null, $text = null, array $attr = []): string
    {
        $number = preg_replace('![^0-9\+]+!', '', $tel);

        if (empty($text) === true) {
            $text = $tel;
        }

        return static::link('tel:' . $number, $text, $attr);
    }

    /**
     * Creates a video embed via iframe for Youtube or Vimeo
     * videos. The embed Urls are automatically detected from
     * the given URL.
     *
     * @param string $url
     * @param array $options
     * @param array $attr
     * @return string
     */
    public static function video(string $url, ?array $options = [], array $attr = []): string
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
     * Embeds a Vimeo video by URL in an iframe
     *
     * @param string $url
     * @param array $options
     * @param array $attr
     * @return string
     */
    public static function vimeo(string $url, ?array $options = [], array $attr = []): string
    {
        if (preg_match('!vimeo.com\/([0-9]+)!i', $url, $array) === 1) {
            $id = $array[1];
        } elseif (preg_match('!player.vimeo.com\/video\/([0-9]+)!i', $url, $array) === 1) {
            $id = $array[1];
        } else {
            throw new Exception('Invalid Vimeo source');
        }

        // build the options query
        if (!empty($options)) {
            $query = '?' . http_build_query($options);
        } else {
            $query = '';
        }

        $url = 'https://player.vimeo.com/video/' . $id . $query;

        return static::iframe($url, array_merge(['allowfullscreen' => true], $attr));
    }

    /**
     * Embeds a Youtube video by URL in an iframe
     *
     * @param string $url
     * @param array $options
     * @param array $attr
     * @return string
     */
    public static function youtube(string $url, ?array $options = [], array $attr = []): string
    {
        // youtube embed domain
        $domain = 'youtube.com';
        $id     = null;

        $schemes = [
            // http://www.youtube.com/embed/d9NF2edxy-M
            ['pattern' => 'youtube.com\/embed\/([a-zA-Z0-9_-]+)'],
            // https://www.youtube-nocookie.com/embed/d9NF2edxy-M
            [
                'pattern' => 'youtube-nocookie.com\/embed\/([a-zA-Z0-9_-]+)',
                'domain'  => 'www.youtube-nocookie.com'
            ],
            // https://www.youtube-nocookie.com/watch?v=d9NF2edxy-M
            [
                'pattern' => 'youtube-nocookie.com\/watch\?v=([a-zA-Z0-9_-]+)',
                'domain'  => 'www.youtube-nocookie.com'
            ],
            // http://www.youtube.com/watch?v=d9NF2edxy-M
            ['pattern' => 'v=([a-zA-Z0-9_-]+)'],
            // http://youtu.be/d9NF2edxy-M
            ['pattern' => 'youtu.be\/([a-zA-Z0-9_-]+)']
        ];

        foreach ($schemes as $schema) {
            if (preg_match('!' . $schema['pattern'] . '!i', $url, $array) === 1) {
                $domain = $schema['domain'] ?? $domain;
                $id     = $array[1];
                break;
            }
        }

        // no match
        if ($id === null) {
            throw new Exception('Invalid Youtube source');
        }

        // build the options query
        if (!empty($options)) {
            $query = '?' . http_build_query($options);
        } else {
            $query = '';
        }

        $url = 'https://' . $domain . '/embed/' . $id . $query;

        return static::iframe($url, array_merge(['allowfullscreen' => true], $attr));
    }
}
