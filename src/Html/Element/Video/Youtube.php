<?php

namespace Kirby\Html\Element\Video;

use Exception;
use Kirby\Html\Element\Video;

/**
 * Embed Youtube videos
 *
 * @package   Kirby Embed
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   MIT
 */
class Youtube extends Video
{

    /**
     * Returns the src URL for the youtube iframe
     *
     * @param  string $src
     * @param  array  $options
     * @return string
     */
    public function src(string $src, array $options = []): string
    {
        // youtube embed domain
        $domain = 'youtube.com';
        $id     = null;

        $schemes = [
            // http://www.youtube.com/embed/d9NF2edxy-M
            ['pattern' => 'youtube.com\/embed\/([a-zA-Z0-9_-]+)'],
            // https://www.youtube-nocookie.com/embed/d9NF2edxy-M
            [
                'pattern' => 'youtube-nocookie.com\/embed\/([a-zA-Z0-9_-]+)',
                'domain'  => 'www.youtube-nocookie.com'
            ],
            // http://www.youtube.com/watch?v=d9NF2edxy-M
            ['pattern' => 'v=([a-zA-Z0-9_-]+)'],
            // http://youtu.be/d9NF2edxy-M
            ['pattern' => 'youtu.be\/([a-zA-Z0-9_-]+)']
        ];

        foreach ($schemes as $schema) {
            if (preg_match('!' . $schema['pattern'] . '!i', $src, $array) === 1) {
                $domain = $schema['domain'] ?? $domain;
                $id     = $array[1];
                break;
            }
        }

        // no match
        if ($id === null) {
            throw new Exception('Invalid Youtube source');
        }

        return '//' . $domain . '/embed/' . $id . $this->query($options);
    }
}
