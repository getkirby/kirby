<?php

namespace Kirby\Html\Element;

use Kirby\Html\Element;

/**
 * Embed Github Gists
 *
 * @package   Kirby Embed
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   MIT
 */
class Gist extends Element
{

    /**
     * Creates a new Gist Element
     *
     * @param string $url
     * @param string $file
     * @param array  $attr
     */
    public function __construct(string $url, string $file = null, array $attr = [])
    {
        parent::__construct('script', '', $attr);
        $this->attr('src', $this->src($url, $file));
    }

    /**
     * Returns the src URL for the script tag
     *
     * @param  string $url
     * @param  string $file
     * @return string
     */
    public function src(string $url, string $file = null): string
    {
        if ($file === null) {
            return $url . '.js';
        } else {
            return $url . '.js?file=' . $file;
        }
    }
}
