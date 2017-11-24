<?php

namespace Kirby\Text\Tags\Tag;

use Kirby\Text\Tags\Tag;

/**
 * The date tag can be used to render
 * the current date in various formats
 *
 * Examples:
 * ```
 * (date: year)
 * (date: d.m.Y)
 * ```
 *
 * @package   Kirby Text
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   MIT
 */
class Date extends Tag
{

    /**
     * Renders the date
     *
     * @return string
     */
    protected function html(): string
    {
        if (strtolower($this->value()) === 'year') {
            return date('Y');
        } else {
            return date($this->value());
        }
    }
}
