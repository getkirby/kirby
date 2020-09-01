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
     * @param string $file
     * @param array $options
     * @return string
     */
    protected function convert(string $file, array $options): string
    {
        return sprintf($options['bin'] . ' "%s"', $file);
    }

    /**
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
     * @param string $file
     * @param array $options
     * @return string
     */
    protected function quality(string $file, array $options): string
    {
        return '-quality ' . $options['quality'];
    }

    /**
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
     * @param string $file
     * @param array $options
     * @return string
     */
    protected function save(string $file, array $options): string
    {
        return sprintf('-limit thread 1 "%s"', $file);
    }

    /**
     * @param string $file
     * @param array $options
     * @return string
     */
    protected function strip(string $file, array $options): string
    {
        return '-strip';
    }
}
