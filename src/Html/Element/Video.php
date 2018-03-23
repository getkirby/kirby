<?php

namespace Kirby\Html\Element;

/**
 * Embed videos
 *
 * @package   Kirby Embed
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   MIT
 */
class Video extends Iframe
{

    /**
     * Custom constructor for iframe elements
     *
     * @param string $src
     * @param array  $attr
     */
    public function __construct(string $src = '', array $options = [], array $attr = [])
    {
        parent::__construct($this->src($src, $options));
        $this->attr('allowfullscreen', true);
        $this->attr($attr);
    }

    /**
     * Returns the src URL for the iframe
     *
     * @param  string  $src
     * @param  array   $options
     * @return string
     */
    public function src(string $src, array $options = []): string
    {
        return $src . $this->query($options);
    }

    /**
     * Builds the query for the URL with the passed options
     * For further information about the options, the API
     * docs for each video platform should be consulted
     *
     * @param  array   $options
     * @return string
     */
    protected function query(array $options): string
    {
        if (!empty($options)) {
            return '?' . http_build_query($options);
        } else {
            return '';
        }
    }

    /**
     * Static helper to create an video embed
     * with automatic detection for YouTube and Vimeo
     *
     * @param  string $src
     * @param  array  $options
     * @param  array  $attr
     * @return Video
     */
    public static function create(string $src = '', array $options = [], array $attr = []): self
    {
        // YouTube video
        if (preg_match('!(youtube.com\/embed\/[a-zA-Z0-9_-]+)|(youtube-nocookie.com\/embed\/[a-zA-Z0-9_-]+)|(v=[a-zA-Z0-9_-]+)|(youtu.be\/[a-zA-Z0-9_-]+)!i', $src) === 1) {
            return new Video\Youtube($src, $options['youtube'] ?? [], $attr);

        // Vimeo video
        } elseif (preg_match('!vimeo.com\/([0-9]+)!i', $src) === 1) {
            return new Video\Vimeo($src, $options['vimeo'] ?? [], $attr);
        }

        return new self($src, $options, $attr);
    }
}
