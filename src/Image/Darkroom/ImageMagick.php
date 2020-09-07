<?php

namespace Kirby\Image\Darkroom;

use Exception;
use Kirby\Image\Darkroom;
use Kirby\Toolkit\F;

/**
 * ImageMagick
 *
 * @package   Kirby Image
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier GmbH
 * @license   https://opensource.org/licenses/MIT
 */
class ImageMagick extends Darkroom
{
    /**
     * Activates imagemagick's auto-orient feature unless
     * it is deactivated via the options
     *
     * @param string $file
     * @param array $options
     * @return string
     */
    protected function autoOrient(string $file, array $options)
    {
        if ($options['autoOrient'] === true) {
            return '-auto-orient';
        }
    }

    /**
     * Applies the blur settings
     *
     * @param string $file
     * @param array $options
     * @return string
     */
    protected function blur(string $file, array $options)
    {
        if ($options['blur'] !== false) {
            return '-blur 0x' . $options['blur'];
        }
    }

    /**
     * Keep animated gifs
     *
     * @param string $file
     * @param array $options
     * @return string
     */
    protected function coalesce(string $file, array $options)
    {
        if (F::extension($file) === 'gif') {
            return '-coalesce';
        }
    }

    /**
     * Creates the convert command with the right path to the binary file
     *
     * @param string $file
     * @param array $options
     * @return string
     */
    protected function convert(string $file, array $options): string
    {
        return sprintf($options['bin'] . ' "%s"', $file);
    }

    /**
     * Returns additional default parameters for imagemagick
     *
     * @return array
     */
    protected function defaults(): array
    {
        return parent::defaults() + [
            'bin'       => 'convert',
            'interlace' => false,
        ];
    }

    /**
     * Applies the correct settings for grayscale images
     *
     * @param string $file
     * @param array $options
     * @return string
     */
    protected function grayscale(string $file, array $options)
    {
        if ($options['grayscale'] === true) {
            return '-colorspace gray';
        }
    }

    /**
     * Applies the correct settings for interlaced JPEGs if
     * activated via options
     *
     * @param string $file
     * @param array $options
     * @return string
     */
    protected function interlace(string $file, array $options)
    {
        if ($options['interlace'] === true) {
            return '-interlace line';
        }
    }

    /**
     * Creates and runs the full imagemagick command
     * to process the image
     *
     * @param string $file
     * @param array $options
     * @return array
     * @throws \Exception
     */
    public function process(string $file, array $options = []): array
    {
        $options = $this->preprocess($file, $options);
        $command = [];

        $command[] = $this->convert($file, $options);
        $command[] = $this->strip($file, $options);
        $command[] = $this->interlace($file, $options);
        $command[] = $this->coalesce($file, $options);
        $command[] = $this->grayscale($file, $options);
        $command[] = $this->autoOrient($file, $options);
        $command[] = $this->resize($file, $options);
        $command[] = $this->quality($file, $options);
        $command[] = $this->blur($file, $options);
        $command[] = $this->save($file, $options);

        // remove all null values and join the parts
        $command = implode(' ', array_filter($command));

        // try to execute the command
        exec($command, $output, $return);

        // log broken commands
        if ($return !== 0) {
            throw new Exception('The imagemagick convert command could not be executed: ' . $command);
        }

        return $options;
    }

    /**
     * Applies the correct JPEG compression quality settings
     *
     * @param string $file
     * @param array $options
     * @return string
     */
    protected function quality(string $file, array $options): string
    {
        return '-quality ' . $options['quality'];
    }

    /**
     * Creates the correct options to crop or resize the image
     * and translates the crop positions for imagemagick
     *
     * @param string $file
     * @param array $options
     * @return string
     */
    protected function resize(string $file, array $options): string
    {
        // simple resize
        if ($options['crop'] === false) {
            return sprintf('-resize %sx%s!', $options['width'], $options['height']);
        }

        $gravities = [
            'top left'     => 'NorthWest',
            'top'          => 'North',
            'top right'    => 'NorthEast',
            'left'         => 'West',
            'center'       => 'Center',
            'right'        => 'East',
            'bottom left'  => 'SouthWest',
            'bottom'       => 'South',
            'bottom right' => 'SouthEast'
        ];

        // translate the gravity option into something imagemagick understands
        $gravity = $gravities[$options['crop']] ?? 'Center';

        $command  = sprintf('-resize %sx%s^', $options['width'], $options['height']);
        $command .= sprintf(' -gravity %s -crop %sx%s+0+0', $gravity, $options['width'], $options['height']);

        return $command;
    }

    /**
     * Makes sure to not process too many images at once
     * which could crash the server
     *
     * @param string $file
     * @param array $options
     * @return string
     */
    protected function save(string $file, array $options): string
    {
        return sprintf('-limit thread 1 "%s"', $file);
    }

    /**
     * Removes all metadata from the image
     *
     * @param string $file
     * @param array $options
     * @return string
     */
    protected function strip(string $file, array $options): string
    {
        return '-strip';
    }
}
