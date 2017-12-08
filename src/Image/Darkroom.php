<?php

namespace Kirby\Image;

abstract class Darkroom
{

    protected $defaults = [];
    protected $options  = [];

    public function __construct(array $defaults = [])
    {
        $this->defaults = array_merge($this->defaults, ['width' => null, 'height' => null], $defaults);
    }

    public function preprocess(string $file, array $options = [])
    {

        $options = array_merge($this->defaults, $options);
        $image   = new Image($file);

        // normalize the quality
        if (($options['quality'] ?? null) === null) {
            $options['quality'] = $this->defaults['quality'] ?? 100;
        }

        // normalize the crop option
        if (($options['crop'] ?? false) === true) {
            $options['crop'] = 'center';
        }

        // normalize the blur option
        if ($options['blur'] === true) {
            $options['blur'] = 10;
        }

        // pre-calculate the correct image size
        if (($options['crop'] ?? false) === false) {
            $dimensions = $image->dimensions()->resize($options['width'], $options['height']);
        } else {
            $dimensions = $image->dimensions()->crop($options['width'], $options['height']);
        }

        $options['width']  = $dimensions->width();
        $options['height'] = $dimensions->height();

        return $options;

    }

    abstract public function process(string $file, array $options = []): array;

    protected function options(array $options = []): array
    {
        return array_merge($this->defaults, $options);
    }

}
