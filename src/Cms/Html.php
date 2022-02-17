<?php

namespace Kirby\Cms;

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
}
