<?php

namespace Kirby\Toolkit;

use Zend\Escaper\Escaper;

/**
 * The `Escape` class provides methods
 * for escaping common HTML attributes
 * data. This can be used to put
 * untrusted data into typical
 * attribute values like width, name,
 * value, etc.
 *
 * Wrapper for the Zend Escaper
 * @link https://github.com/zendframework/zend-escaper
 *
 * @package   Kirby Toolkit
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier GmbH
 * @license   https://opensource.org/licenses/MIT
 */
class Escape
{
    /**
     * Escape common HTML attributes data
     *
     * This can be used to put untrusted data into typical attribute values
     * like width, name, value, etc.
     *
     * This should not be used for complex attributes like href, src, style,
     * or any of the event handlers like onmouseover.
     * Use esc($string, 'js') for event handler attributes, esc($string, 'url')
     * for src attributes and esc($string, 'css') for style attributes.
     *
     * <div attr=...ESCAPE UNTRUSTED DATA BEFORE PUTTING HERE...>content</div>
     * <div attr='...ESCAPE UNTRUSTED DATA BEFORE PUTTING HERE...'>content</div>
     * <div attr="...ESCAPE UNTRUSTED DATA BEFORE PUTTING HERE...">content</div>
     *
     * @param  string $string
     * @return string
     */
    public static function attr($string)
    {
        return (new Escaper('utf-8'))->escapeHtmlAttr($string);
    }

    /**
     * Escape HTML style property values
     *
     * This can be used to put untrusted data into a stylesheet or a style tag.
     *
     * Stay away from putting untrusted data into complex properties like url,
     * behavior, and custom (-moz-binding). You should also not put untrusted data
     * into IE’s expression property value which allows JavaScript.
     *
     * <style>selector { property : ...ESCAPE UNTRUSTED DATA BEFORE PUTTING HERE...; } </style>
     * <style>selector { property : "...ESCAPE UNTRUSTED DATA BEFORE PUTTING HERE..."; } </style>
     * <span style="property : ...ESCAPE UNTRUSTED DATA BEFORE PUTTING HERE...">text</span>
     *
     * @param  string $string
     * @return string
     */
    public static function css($string)
    {
        return (new Escaper('utf-8'))->escapeCss($string);
    }

    /**
     * Escape HTML element content
     *
     * This can be used to put untrusted data directly into the HTML body somewhere.
     * This includes inside normal tags like div, p, b, td, etc.
     *
     * Escapes &, <, >, ", and ' with HTML entity encoding to prevent switching
     * into any execution context, such as script, style, or event handlers.
     *
     * <body>...ESCAPE UNTRUSTED DATA BEFORE PUTTING HERE...</body>
     * <div>...ESCAPE UNTRUSTED DATA BEFORE PUTTING HERE...</div>
     *
     * @param  string $string
     * @return string
     */
    public static function html($string)
    {
        return (new Escaper('utf-8'))->escapeHtml($string);
    }

    /**
     * Escape JavaScript data values
     *
     * This can be used to put dynamically generated JavaScript code
     * into both script blocks and event-handler attributes.
     *
     * <script>alert('...ESCAPE UNTRUSTED DATA BEFORE PUTTING HERE...')</script>
     * <script>x='...ESCAPE UNTRUSTED DATA BEFORE PUTTING HERE...'</script>
     * <div onmouseover="x='...ESCAPE UNTRUSTED DATA BEFORE PUTTING HERE...'"</div>
     *
     * @param  string $string
     * @return string
     */
    public static function js($string)
    {
        return (new Escaper('utf-8'))->escapeJs($string);
    }

    /**
     * Escape URL parameter values
     *
     * This can be used to put untrusted data into HTTP GET parameter values.
     * This should not be used to escape an entire URI.
     *
     * <a href="http://www.somesite.com?test=...ESCAPE UNTRUSTED DATA BEFORE PUTTING HERE...">link</a>
     *
     * @param string  $string
     * @return string
     */
    public static function url($string)
    {
        return rawurlencode($string);
    }

    /**
     * Escape XML element content
     *
     * Removes offending characters that could be wrongfully interpreted as XML markup.
     *
     * The following characters are reserved in XML and will be replaced with their
     * corresponding XML entities:
     *
     * ' is replaced with &apos;
     * " is replaced with &quot;
     * & is replaced with &amp;
     * < is replaced with &lt;
     * > is replaced with &gt;
     *
     * @param  string $string
     * @return string
     */
    public static function xml($string)
    {
        return htmlspecialchars($string, ENT_QUOTES | ENT_XML1, 'UTF-8');
    }
}
