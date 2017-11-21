<?php

namespace Kirby\Html\Element;

use Kirby\Html\Element;

/**
 * The iFrame Element helps
 * setting up iFrames more
 * conveniently.
 *
 * @package   Kirby Html
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   MIT
 */
class Iframe extends Element
{

    /**
     * Custom constructor for iframe elements
     *
     * @param string $src
     * @param array  $attr
     */
    public function __construct(string $src = '', array $attr = [])
    {
        parent::__construct('iframe');

        $this->attr('width', '100%');
        $this->attr('height', '100%');
        $this->attr('border', '0');
        $this->attr('frameborder', '0');
        $this->attr($attr);
        $this->attr('src', $src);
    }
}
