<?php

namespace Kirby\Image;

use Exception;

/**
 * A wrapper around resizing and cropping
 * via GDLib, ImageMagick or other libraries.
 */
class Darkroom
{
    public static $types = [
        'gd' => 'Kirby\Image\Darkroom\GdLib',
        'im' => 'Kirby\Image\Darkroom\ImageMagick'
    ];

    protected $settings = [];

    public function __construct(array $settings = [])
    {
        $this->settings = array_merge($this->defaults(), $settings);
    }

    public static function factory(string $type, array $settings = [])
    {
        if (isset(static::$types[$type]) === false) {
            throw new Exception('Invalid Darkroom type');
        }

        $class = static::$types[$type];
        return new $class($settings);
    }

    protected function defaults(): array
    {
        return [
            'autoOrient' => true,
            'crop'       => false,
            'blur'       => false,
            'grayscale'  => false,
            'height'     => null,
            'quality'    => 90,
            'width'      => null,
        ];
    }

    protected function options(array $options = []): array
    {
        $options = array_merge($this->settings, $options);

        // normalize the crop option
        if ($options['crop'] === true) {
            $options['crop'] = 'center';
        }

        // normalize the blur option
        if ($options['blur'] === true) {
            $options['blur'] = 10;
        }

        if ($options['quality'] === null) {
            $options['quality'] = $this->settings['quality'];
        }

        return $options;
    }

    public function preprocess(string $file, array $options = [])
    {
        $options = $this->options($options);
        $image   = new Image($file);

        // pre-calculate the correct image size
        if ($options['crop'] === false) {
            $dimensions = $image->dimensions()->resize($options['width'], $options['height']);
        } else {
            $dimensions = $image->dimensions()->crop($options['width'], $options['height']);
        }

        $options['width']  = $dimensions->width();
        $options['height'] = $dimensions->height();

        return $options;
    }

    public function process(string $file, array $options = []): array
    {
        return $this->preprocess($file, $options);
    }
}
