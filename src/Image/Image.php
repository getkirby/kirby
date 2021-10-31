<?php

namespace Kirby\Image;

use Kirby\Filesystem\File;
use Kirby\Toolkit\Html;

/**
 * A representation of an image file
 * with dimensions, optional exif data and
 * a connection to our darkroom classes to resize/crop
 * images.
 *
 * Extends the `Kirby\Filesystem\File` class with
 * those image-specific methods.
 *
 * @package   Kirby Image
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier GmbH
 * @license   https://opensource.org/licenses/MIT
 */
class Image extends File
{
    /**
     * @var \Kirby\Image\Exif|null
     */
    protected $exif;

    /**
     * @var \Kirby\Image\Dimensions|null
     */
    protected $dimensions;

    /**
     * @var array
     */
    public static $resizableTypes = [
        'jpg',
        'jpeg',
        'gif',
        'png',
        'webp'
    ];

    /**
     * @var array
     */
    public static $viewableTypes = [
        'avif',
        'jpg',
        'jpeg',
        'gif',
        'png',
        'svg',
        'webp'
    ];

    /**
     * Validation rules to be used for `::match()`
     *
     * @var array
     */
    public static $validations = [
        'maxsize'     => ['size',   'max'],
        'minsize'     => ['size',   'min'],
        'maxwidth'    => ['width',  'max'],
        'minwidth'    => ['width',  'min'],
        'maxheight'   => ['height', 'max'],
        'minheight'   => ['height', 'min'],
        'orientation' => ['orientation', 'same']
    ];

    /**
     * Returns the `<img>` tag for the image object
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->html();
    }

    /**
     * Returns the dimensions of the file if possible
     *
     * @return \Kirby\Image\Dimensions
     */
    public function dimensions()
    {
        if ($this->dimensions !== null) {
            return $this->dimensions;
        }

        if (in_array($this->mime(), [
            'image/jpeg',
            'image/jp2',
            'image/png',
            'image/gif',
            'image/webp'
        ])) {
            return $this->dimensions = Dimensions::forImage($this->root);
        }

        if ($this->extension() === 'svg') {
            return $this->dimensions = Dimensions::forSvg($this->root);
        }

        return $this->dimensions = new Dimensions(0, 0);
    }

    /**
     * Returns the exif object for this file (if image)
     *
     * @return \Kirby\Image\Exif
     */
    public function exif()
    {
        return $this->exif ??= new Exif($this);
    }

    /**
     * Returns the height of the asset
     *
     * @return int
     */
    public function height(): int
    {
        return $this->dimensions()->height();
    }

    /**
     * Converts the file to html
     *
     * @param array $attr
     * @return string
     */
    public function html(array $attr = []): string
    {
        return Html::img($this->url(), $attr);
    }

    /**
     * Returns the PHP imagesize array
     *
     * @return array
     */
    public function imagesize(): array
    {
        return getimagesize($this->root);
    }

    /**
     * Checks if the dimensions of the asset are portrait
     *
     * @return bool
     */
    public function isPortrait(): bool
    {
        return $this->dimensions()->portrait();
    }

    /**
     * Checks if the dimensions of the asset are landscape
     *
     * @return bool
     */
    public function isLandscape(): bool
    {
        return $this->dimensions()->landscape();
    }

    /**
     * Checks if the dimensions of the asset are square
     *
     * @return bool
     */
    public function isSquare(): bool
    {
        return $this->dimensions()->square();
    }

    /**
     * Checks if the file is a resizable image
     *
     * @return bool
     */
    public function isResizable(): bool
    {
        return in_array($this->extension(), static::$resizableTypes) === true;
    }

    /**
     * Checks if a preview can be displayed for the file
     * in the Panel or in the frontend
     *
     * @return bool
     */
    public function isViewable(): bool
    {
        return in_array($this->extension(), static::$viewableTypes) === true;
    }

    /**
     * Returns the ratio of the asset
     *
     * @return float
     */
    public function ratio(): float
    {
        return $this->dimensions()->ratio();
    }

    /**
     * Returns the orientation as string
     * landscape | portrait | square
     *
     * @return string
     */
    public function orientation(): string
    {
        return $this->dimensions()->orientation();
    }

    /**
     * Converts the object to an array
     *
     * @return array
     */
    public function toArray(): array
    {
        $array = array_merge(parent::toArray(), [
            'dimensions' => $this->dimensions()->toArray(),
            'exif'       => $this->exif()->toArray(),
        ]);

        ksort($array);

        return $array;
    }

    /**
     * Returns the width of the asset
     *
     * @return int
     */
    public function width(): int
    {
        return $this->dimensions()->width();
    }
}
