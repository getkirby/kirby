<?php

namespace Kirby\Cms;

use Kirby\Exception\InvalidArgumentException;

/**
 * Resizing, blurring etc.
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier GmbH
 * @license   https://getkirby.com/license
 */
trait FileModifications
{
    /**
     * Blurs the image by the given amount of pixels
     *
     * @param bool $pixels
     * @return \Kirby\Cms\FileVersion|\Kirby\Cms\File
     */
    public function blur($pixels = true)
    {
        return $this->thumb(['blur' => $pixels]);
    }

    /**
     * Converts the image to black and white
     *
     * @return \Kirby\Cms\FileVersion|\Kirby\Cms\File
     */
    public function bw()
    {
        return $this->thumb(['grayscale' => true]);
    }

    /**
     * Crops the image by the given width and height
     *
     * @param int $width
     * @param int|null $height
     * @param string|array $options
     * @return \Kirby\Cms\FileVersion|\Kirby\Cms\File
     */
    public function crop(int $width, int $height = null, $options = null)
    {
        $quality = null;
        $crop    = 'center';

        if (is_int($options) === true) {
            $quality = $options;
        } elseif (is_string($options)) {
            $crop = $options;
        } elseif (is_a($options, 'Kirby\Cms\Field') === true) {
            $crop = $options->value();
        } elseif (is_array($options)) {
            $quality = $options['quality'] ?? $quality;
            $crop    = $options['crop']    ?? $crop;
        }

        return $this->thumb([
            'width'   => $width,
            'height'  => $height,
            'quality' => $quality,
            'crop'    => $crop
        ]);
    }

    /**
     * Alias for File::bw()
     *
     * @return \Kirby\Cms\FileVersion|\Kirby\Cms\File
     */
    public function grayscale()
    {
        return $this->thumb(['grayscale' => true]);
    }

    /**
     * Alias for File::bw()
     *
     * @return \Kirby\Cms\FileVersion|\Kirby\Cms\File
     */
    public function greyscale()
    {
        return $this->thumb(['grayscale' => true]);
    }

    /**
     * Sets the JPEG compression quality
     *
     * @param int $quality
     * @return \Kirby\Cms\FileVersion|\Kirby\Cms\File
     */
    public function quality(int $quality)
    {
        return $this->thumb(['quality' => $quality]);
    }

    /**
     * Resizes the file with the given width and height
     * while keeping the aspect ratio.
     *
     * @param int|null $width
     * @param int|null $height
     * @param int|null $quality
     * @return \Kirby\Cms\FileVersion|\Kirby\Cms\File
     * @throws \Kirby\Exception\InvalidArgumentException
     */
    public function resize(int $width = null, int $height = null, int $quality = null)
    {
        return $this->thumb([
            'width'   => $width,
            'height'  => $height,
            'quality' => $quality
        ]);
    }

    /**
     * Create a srcset definition for the given sizes
     * Sizes can be defined as a simple array. They can
     * also be set up in the config with the thumbs.srcsets option.
     * @since 3.1.0
     *
     * @param array|string|null $sizes
     * @return string|null
     */
    public function srcset($sizes = null): ?string
    {
        if (empty($sizes) === true) {
            $sizes = $this->kirby()->option('thumbs.srcsets.default', []);
        }

        if (is_string($sizes) === true) {
            $sizes = $this->kirby()->option('thumbs.srcsets.' . $sizes, []);
        }

        if (is_array($sizes) === false || empty($sizes) === true) {
            return null;
        }

        $set = [];

        foreach ($sizes as $key => $value) {
            if (is_array($value)) {
                $options = $value;
                $condition = $key;
            } elseif (is_string($value) === true) {
                $options = [
                    'width' => $key
                ];
                $condition = $value;
            } else {
                $options = [
                    'width' => $value
                ];
                $condition = $value . 'w';
            }

            $set[] = $this->thumb($options)->url() . ' ' . $condition;
        }

        return implode(', ', $set);
    }

    /**
     * Creates a modified version of images
     * The media manager takes care of generating
     * those modified versions and putting them
     * in the right place. This is normally the
     * `/media` folder of your installation, but
     * could potentially also be a CDN or any other
     * place.
     *
     * @param array|null|string $options
     * @return \Kirby\Cms\FileVersion|\Kirby\Cms\File
     * @throws \Kirby\Exception\InvalidArgumentException
     */
    public function thumb($options = null)
    {
        // thumb presets
        if (empty($options) === true) {
            $options = $this->kirby()->option('thumbs.presets.default');
        } elseif (is_string($options) === true) {
            $options = $this->kirby()->option('thumbs.presets.' . $options);
        }

        if (empty($options) === true || is_array($options) === false) {
            return $this;
        }

        $result = ($this->kirby()->component('file::version'))($this->kirby(), $this, $options);

        if (is_a($result, 'Kirby\Cms\FileVersion') === false && is_a($result, 'Kirby\Cms\File') === false) {
            throw new InvalidArgumentException('The file::version component must return a File or FileVersion object');
        }

        return $result;
    }
}
