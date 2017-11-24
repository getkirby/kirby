<?php

namespace Kirby\Html\Element;

use Kirby\Html\Element;

/**
 * The Img Element is an extension
 * for Kirby's HTML Element, which
 * can be used to create image tags
 * more easily.
 *
 * @package   Kirby Html
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   MIT
 */
class Img extends Element
{

    /**
     * Custom constructor for image elements
     *
     * @param string $src
     * @param array  $attr
     */
    public function __construct(string $src = '', array $attr = [])
    {
        parent::__construct('img');

        $this->attr('alt', ' ');
        $this->attr($attr);
        $this->attr('src', $src);
    }
}
