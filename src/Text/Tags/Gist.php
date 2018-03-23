<?php

namespace Kirby\Text\Tags;

use Kirby\Html\Element\Gist as Embed;

/**
 * Embeds a Github Gist in your text
 *
 * Example
 * ```
 * (gist: https://gist.github.com/bastianallgeier/3733bbec13cc635d4c9d7a9afa34f144)
 * ```
 *
 * @package   Kirby Text
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   MIT
 */
class Gist extends Tag
{

    /**
     * Returns the embed code for the Github Gist
     *
     * @return string
     */
    protected function html(): string
    {
        return new Embed($this->value());
    }
}
