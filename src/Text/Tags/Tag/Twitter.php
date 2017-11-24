<?php

namespace Kirby\Text\Tags\Tag;

use Kirby\Text\Tags\Tag;

/**
 * Creates a Twitter link in your text
 *
 * Examples:
 * ```
 * (twitter: getkirby)
 * (twitter: getkirby text: Follow Us!)
 * ```
 *
 * Check out the Link class for all
 * available attributes.
 *
 * @package   Kirby Text
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   MIT
 */
class Twitter extends Link
{

    /**
     * Returns the plain username
     * without @ prefix.
     *
     * @return string
     */
    protected function username(): string
    {
        return str_replace('@', '', $this->value());
    }

    /**
     * Returns the absolute link to the Twitter profile
     *
     * @return string
     */
    protected function link(): string
    {
        return 'https://twitter.com/' . $this->username();
    }

    /**
     * Returns the text attribute value or
     * the twitter username
     *
     * @return string
     */
    protected function text(): string
    {
        return $this->attr('text', '@' . $this->username());
    }
}
