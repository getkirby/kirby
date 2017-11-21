<?php

namespace Kirby\Text\Tags\Tag;

use Kirby\Html\Element\Video\Youtube as Embed;
use Kirby\Text\Tags\Tag;

/**
 * Embeds a Youtube video in your text
 *
 * Example:
 * ```
 * (youtube: https://www.youtube.com/watch?v=7_FeYJaiAh4)
 * ```
 *
 * @package   Kirby Text
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   MIT
 */
class Youtube extends Tag
{

    /**
     * Uses Kirby\Embed\Video\Youtube to
     * render the Youtube iframe
     *
     * @return string
     */
    protected function html(): string
    {
        return new Embed($this->value());
    }
}
