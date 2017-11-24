<?php

namespace Kirby\Html\Element;

use Kirby\Html\Element;

/**
 * The Link Element is an extension of
 * Kirby's HTML Element, which can be
 * used to create links more easily.
 *
 * @package   Kirby Html
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   MIT
 */
class A extends Element
{

    /**
     * Custom constructor for link elements
     *
     * @param string $href
     * @param string $html
     * @param array  $attr
     */
    public function __construct(string $href = '/', string $html = '', array $attr = [])
    {
        parent::__construct('a');

        $this->html($html);
        $this->attr($attr);
        $this->attr('href', $href);
    }
}
