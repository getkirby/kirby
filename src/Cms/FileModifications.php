<?php

namespace Kirby\Cms;

/**
 * Resizing, blurring etc
 */
trait FileModifications
{

    /**
     * Blurs the image by the given amount of pixels
     *
     * @param boolean $pixels
     * @return self
     */
    public function blur($pixels = true)
    {
        return $this->thumb(['blur' => $pixels]);
    }

    /**
     * Converts the image to black and white
     *
     * @return self
     */
    public function bw()
    {
        return $this->thumb(['grayscale' => true]);
    }

    /**
     * Crops the image by the given width and height
     *
     * @param integer $width
     * @param integer $height
     * @param string|array $options
     * @return self
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
     * Sets the JPEG compression quality
     *
     * @param integer $quality
     * @return self
     */
    public function quality(int $quality)
    {
        return $this->thumb(['quality' => $quality]);
    }

    /**
     * Resizes the file with the given width and height
     * while keeping the aspect ratio.
     *
     * @param integer $width
     * @param integer $height
     * @param integer $quality
     * @return self
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
     * Creates a modified version of images
     * The media manager takes care of generating
     * those modified versions and putting them
     * in the right place. This is normally the
     * /media folder of your installation, but
     * could potentially also be a CDN or any other
     * place.
     *
     * @param array|null|string $options
     * @return FileVersion|File
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

        $result = $this->kirby()->component('file::version')($this->kirby(), $this, $options);

        if (is_a($result, FileVersion::class) === false && is_a($result, File::class) === false) {
            throw new InvalidArgumentException('The file::version component must return a File or FileVersion object');
        }

        return $result;
    }
}
