<?php

namespace Kirby\Text\Tags;

use Kirby\Html\Element;
use Kirby\Html\Element\Video as Iframe;
use Kirby\Util\Str;

/**
 * Embeds a Vimeo or Youtube video in your text
 *
 * Example:
 * ```
 * (video: https://vimeo.com/217697296)
 * ```
 *
 * @package   Kirby Text
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   MIT
 */
class Video extends Tag
{

    protected function caption()
    {
        if ($caption = $this->attr('caption')) {
            return new Element('figcaption', $caption);
        }
    }

    protected function iframe(): Element
    {
        return Iframe::create($this->value());
    }

    /**
     * Uses Kirby\Embed\Video\Vimeo to
     * render the Vimeo iframe
     *
     * @return string
     */
    protected function html(): string
    {
        $figure = new Element('figure', [
            'class' => $this->attr('class')
        ]);

        $figure->html([
            $this->iframe(),
            $this->caption()
        ]);

        return $figure;
    }
}
