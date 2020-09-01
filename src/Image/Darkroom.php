<?php

namespace Kirby\Image;

use Exception;

/**
 * A wrapper around resizing and cropping
 * via GDLib, ImageMagick or other libraries.
 *
 * @package   Kirby Image
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier GmbH
 * @license   https://opensource.org/licenses/MIT
 */
class Darkroom
{
    public static $types = [
        'gd' => 'Kirby\Image\Darkroom\GdLib',
        'im' => 'Kirby\Image\Darkroom\ImageMagick'
    ];

    protected $settings = [];

    /**
     * Darkroom constructor
     *
     * @param array $settings
     */
    public function __construct(array $settings = [])
    {
        $this->settings = array_merge($this->defaults(), $settings);
    }

    /**
     * @param string $type
     * @param array $settings
     * @return mixed
     * @throws \Exception
     */
    public static function factory(string $type, array $settings = [])
    {
        if (isset(static::$types[$type]) === false) {
            throw new Exception('Invalid Darkroom type');
        }

        $class = static::$types[$type];
        return new $class($settings);
    }

    /**
     * @return array
     */
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

    /**
     * @param array $options
     * @return array
     */
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

        // normalize the greyscale option
        if (isset($options['greyscale']) === true) {
            $options['grayscale'] = $options['greyscale'];
            unset($options['greyscale']);
        }

        // normalize the bw option
        if (isset($options['bw']) === true) {
            $options['grayscale'] = $options['bw'];
            unset($options['bw']);
        }

        if ($options['quality'] === null) {
            $options['quality'] = $this->settings['quality'];
        }

        return $options;
    }

    /**
     * @param string $file
     * @param array $options
     * @return array
     */
    public function preprocess(string $file, array $options = [])
    {
        $options    = $this->options($options);
        $image      = new Image($file);
        $dimensions = $image->dimensions()->thumb($options);

        $options['width']  = $dimensions->width();
        $options['height'] = $dimensions->height();

        return $options;
    }

    /**
     * @param string $file
     * @param array $options
     * @return array
     */
    public function process(string $file, array $options = []): array
    {
        return $this->preprocess($file, $options);
    }
}
