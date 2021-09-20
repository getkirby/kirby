<?php

namespace Kirby\Sane;

use Kirby\Exception\Exception;
use Kirby\Toolkit\Dom;

/**
 * Sane handler for HTML files
 * @since 3.6.0
 *
 * @package   Kirby Sane
 * @author    Bastian Allgeier <bastian@getkirby.com>,
 *            Lukas Bestle <lukas@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier GmbH
 * @license   https://opensource.org/licenses/MIT
 */
class Html extends Handler
{
    /**
     * List of allowed elements
     *
     * @var array
     */
    public static $allowed = [
        'a'          => ['href', 'title', 'target'],
        'abbr'       => ['title'],
        'b'          => true,
        'body'       => true,
        'blockquote' => true,
        'br'         => true,
        'dl'         => true,
        'dd'         => true,
        'del'        => true,
        'div'        => true,
        'dt'         => true,
        'em'         => true,
        'h1'         => true,
        'h2'         => true,
        'h3'         => true,
        'h4'         => true,
        'h5'         => true,
        'h6'         => true,
        'hr'         => true,
        'html'       => true,
        'i'          => true,
        'ins'        => true,
        'li'         => true,
        'strong'     => true,
        'sub'        => true,
        'sup'        => true,
        'ol'         => true,
        'p'          => true,
        'ul'         => true,
    ];

    /**
     * List of disallowed elements
     *
     * @var array
     */
    public static $disallowed = [
        'iframe',
        'meta',
        'object',
        'script',
        'style',
    ];

    /**
     * List of attributes that might contain URLs
     *
     * @var array
     */
    public static $urls = [
        'href',
        'src',
        'xlink:href',
    ];

    /**
     * HTML sanitization
     *
     * @param string $string
     * @return string
     */
    public static function sanitize(string $string): string
    {
        $doc = new Dom('<body>' . $string . '</body>', 'html');
        $doc->sanitize([
            'allowedAttrs'   => ['class', 'id'],
            'allowedTags'    => static::$allowed,
            'disallowedTags' => static::$disallowed,
            'urlAttrs'       => static::$urls,
        ]);

        return $doc->innerMarkup($doc->body());
    }

    /**
     * Validates file contents
     * (Not yet implemented)
     *
     * @param string $string
     * @return void
     */
    public static function validate(string $string): void
    {
        throw new Exception('HTML validation is not yet implemented');
    }
}
