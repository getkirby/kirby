<?php

namespace Kirby\Cms;

use Kirby\Toolkit\Str;

/**
 * The Filename class handles complex
 * mapping of file attributes (i.e for thumbnails)
 * into human readable filenames.
 *
 * ```php
 * $filename = new Filename('some-file.jpg', '{{ name }}-{{ attributes }}.{{ extension }}', [
 *   'crop'    => 'top left',
 *   'width'   => 300,
 *   'height'  => 200
 *   'quality' => 80
 * ]);
 *
 * echo $filename->toString();
 * // result: some-file-300x200-crop-top-left-q80.jpg
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 */
class Filename
{

    /**
     * List of all applicable attributes
     *
     * @var array
     */
    protected $attributes;

    /**
     * The sanitized file extension
     *
     * @var string
     */
    protected $extension;

    /**
     * The source original filename
     *
     * @var string
     */
    protected $filename;

    /**
     * The sanitized file name
     *
     * @var string
     */
    protected $name;

    /**
     * The template for the final name
     *
     * @var string
     */
    protected $template;

    /**
     * Creates a new Filename object
     *
     * @param string $filename
     * @param string $template
     * @param array  $attributes
     */
    public function __construct(string $filename, string $template, array $attributes = [])
    {
        $this->filename   = $filename;
        $this->template   = $template;
        $this->attributes = $attributes;
        $this->extension  = $this->sanitizeExtension(pathinfo($filename, PATHINFO_EXTENSION));
        $this->name       = $this->sanitizeName(pathinfo($filename, PATHINFO_FILENAME));
    }

    /**
     * Converts the entire object to a string
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->toString();
    }

    /**
     * Converts all processed attributes
     * to an array. The array keys are already
     * the shortened versions for the filename
     *
     * @return array
     */
    public function attributesToArray(): array
    {
        $array = [
            'dimensions' => implode('x', $this->dimensions()),
            'crop'       => $this->crop(),
            'blur'       => $this->blur(),
            'bw'         => $this->grayscale(),
            'q'          => $this->quality(),
        ];

        $array = array_filter($array, function ($item) {
            return $item !== null && $item !== false && $item !== '';
        });

        return $array;
    }

    /**
     * Converts all processed attributes
     * to a string, that can be used in the
     * new filename
     *
     * @param  string $prefix The prefix will be used in the filename creation
     * @return string
     */
    public function attributesToString(string $prefix = null): string
    {
        $array  = $this->attributesToArray();
        $result = [];

        foreach ($array as $key => $value) {
            if ($value === true) {
                $value = '';
            }

            switch ($key) {
                case 'dimensions':
                    $result[] = $value;
                    break;
                case 'crop':
                    $result[] = ($value === 'center') ? null : $key . '-' . $value;
                    break;
                default:
                    $result[] = $key . $value;
            }
        }

        $result     = array_filter($result);
        $attributes = implode('-', $result);

        if (empty($attributes) === true) {
            return '';
        }

        return $prefix . $attributes;
    }

    /**
     * Normalizes the blur option value
     *
     * @return false|int
     */
    public function blur()
    {
        $value = $this->attributes['blur'] ?? false;

        if ($value === false) {
            return false;
        }

        return intval($value);
    }

    /**
     * Normalizes the crop option value
     *
     * @return false|string
     */
    public function crop()
    {
        // get the crop value
        $crop = $this->attributes['crop'] ?? false;

        if ($crop === false) {
            return false;
        }

        return Str::slug($crop);
    }

    /**
     * Returns a normalized array
     * with width and height values
     * if available
     *
     * @return void
     */
    public function dimensions()
    {
        if (empty($this->attributes['width']) === true && empty($this->attributes['height']) === true) {
            return [];
        }

        return [
            'width'  => $this->attributes['width']  ?? null,
            'height' => $this->attributes['height'] ?? null
        ];
    }

    /**
     * Returns the sanitized extension
     *
     * @return string
     */
    public function extension(): string
    {
        return $this->extension;
    }

    /**
     * Normalizes the grayscale option value
     * and also the available ways to write
     * the option. You can use `grayscale`,
     * `greyscale` or simply `bw`. The function
     * will always return `grayscale`
     *
     * @return bool
     */
    public function grayscale(): bool
    {
        // normalize options
        $value = $this->attributes['grayscale'] ?? $this->attributes['greyscale'] ?? $this->attributes['bw'] ?? false;

        // turn anything into boolean
        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Returns the filename without extension
     *
     * @return string
     */
    public function name(): string
    {
        return $this->name;
    }

    /**
     * Normalizes the quality option value
     *
     * @return false|int
     */
    public function quality()
    {
        $value = $this->attributes['quality'] ?? false;

        if ($value === false || $value === true) {
            return false;
        }

        return intval($value);
    }

    /**
     * Sanitizes the file extension.
     * The extension will be converted
     * to lowercase and `jpeg` will be
     * replaced with `jpg`
     *
     * @param  string $extension
     * @return string
     */
    protected function sanitizeExtension(string $extension): string
    {
        $extension = strtolower($extension);
        $extension = str_replace('jpeg', 'jpg', $extension);
        return $extension;
    }

    /**
     * Sanitizes the name with Kirby's
     * Str::slug function
     *
     * @param  string $name
     * @return string
     */
    protected function sanitizeName(string $name): string
    {
        return Str::slug($name);
    }

    /**
     * Returns the converted filename as string
     *
     * @return string
     */
    public function toString(): string
    {
        return Str::template($this->template, [
            'name'       => $this->name(),
            'attributes' => $this->attributesToString('-'),
            'extension'  => $this->extension()
        ]);
    }
}
