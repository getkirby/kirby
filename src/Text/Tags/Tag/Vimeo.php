<?php

namespace Kirby\Text\Tags\Tag;

use Kirby\Html\Element\Video\Vimeo as Embed;
use Kirby\Text\Tags\Tag;

/**
 * Embeds a Vimeo video in your text
 *
 * Example:
 * ```
 * (vimeo: https://vimeo.com/217697296)
 * ```
 *
 * @package   Kirby Text
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   MIT
 */
class Vimeo extends Tag
{

    /**
     * Uses Kirby\Embed\Video\Vimeo to
     * render the Vimeo iframe
     *
     * @return string
     */
    protected function html(): string
    {
        return new Embed($this->value());
    }
}
