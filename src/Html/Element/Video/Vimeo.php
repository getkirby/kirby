<?php

namespace Kirby\Html\Element\Video;

use Exception;
use Kirby\Html\Element\Video;

/**
 * Embed Vimeo videos
 *
 * @package   Kirby Embed
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   MIT
 */
class Vimeo extends Video
{

    /**
     * Returns the src URL for the Vimeo iframe
     *
     * @param  string $src
     * @param  array  $options
     * @return string
     */
    public function src(string $src, array $options = []): string
    {
        if (preg_match('!vimeo.com\/([0-9]+)!i', $src, $array) === 1) {
            $id = $array[1];
        } else {
            throw new Exception('Invalid Vimeo source');
        }

        return '//player.vimeo.com/video/' . $id . $this->query($options);
    }
}
