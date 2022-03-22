<?php

namespace Kirby\Image\Darkroom;

use claviska\SimpleImage;
use Kirby\Filesystem\Mime;
use Kirby\Image\Darkroom;

/**
 * GdLib
 *
 * @package   Kirby Image
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
class GdLib extends Darkroom
{
    /**
     * Processes the image with the SimpleImage library
     *
     * @param string $file
     * @param array $options
     * @return array
     */
    public function process(string $file, array $options = []): array
    {
        $options = $this->preprocess($file, $options);
        $mime    = $this->mime($options);

        $image = new SimpleImage();
        $image->fromFile($file);

        $image = $this->resize($image, $options);
        $image = $this->autoOrient($image, $options);
        $image = $this->blur($image, $options);
        $image = $this->grayscale($image, $options);

        $image->toFile($file, $mime, $options['quality']);

        return $options;
    }

    /**
     * Activates the autoOrient option in SimpleImage
     * unless this is deactivated
     *
     * @param \claviska\SimpleImage $image
     * @param $options
     * @return \claviska\SimpleImage
     */
    protected function autoOrient(SimpleImage $image, $options)
    {
        if ($options['autoOrient'] === false) {
            return $image;
        }

        return $image->autoOrient();
    }

    /**
     * Wrapper around SimpleImage's resize and crop methods
     *
     * @param \claviska\SimpleImage $image
     * @param array $options
     * @return \claviska\SimpleImage
     */
    protected function resize(SimpleImage $image, array $options)
    {
        if ($options['crop'] === false) {
            return $image->resize($options['width'], $options['height']);
        }

        return $image->thumbnail($options['width'], $options['height'] ?? $options['width'], $options['crop']);
    }

    /**
     * Applies the correct blur settings for SimpleImage
     *
     * @param \claviska\SimpleImage $image
     * @param array $options
     * @return \claviska\SimpleImage
     */
    protected function blur(SimpleImage $image, array $options)
    {
        if ($options['blur'] === false) {
            return $image;
        }

        return $image->blur('gaussian', (int)$options['blur']);
    }

    /**
     * Applies grayscale conversion if activated in the options.
     *
     * @param \claviska\SimpleImage $image
     * @param array $options
     * @return \claviska\SimpleImage
     */
    protected function grayscale(SimpleImage $image, array $options)
    {
        if ($options['grayscale'] === false) {
            return $image;
        }

        return $image->desaturate();
    }

    /**
     * Returns mime type based on `format` option
     *
     * @param array $options
     * @return string|null
     */
    protected function mime(array $options): ?string
    {
        if ($options['format'] === null) {
            return null;
        }

        return Mime::fromExtension($options['format']);
    }
}
