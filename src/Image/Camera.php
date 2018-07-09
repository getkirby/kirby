<?php

namespace Kirby\Image;

/**
* Small class which hold info about the camera
*
* @package   Kirby Toolkit
* @author    Bastian Allgeier <bastian@getkirby.com>
* @link      http://getkirby.com
* @copyright Bastian Allgeier
* @license   MIT
*/
class Camera
{

    /**
     * Make exif data
     *
     * @var string|null
     */
    protected $make;

    /**
     * Model exif data
     *
     * @var string|null
     */
    protected $model;

    /**
     * Constructor
     *
     * @param array $exif
     */
    public function __construct(array $exif)
    {
        $this->make  = @$exif['Make'];
        $this->model = @$exif['Model'];
    }

    /**
     * Returns the make of the camera
     *
     * @return string
     */
    public function make(): ?string
    {
        return $this->make;
    }

    /**
     * Returns the camera model
     *
     * @return string
     */
    public function model(): ?string
    {
        return $this->model;
    }

    /**
     * Converts the object into a nicely readable array
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'make'  => $this->make,
            'model' => $this->model
        ];
    }

    /**
     * Returns the full make + model name
     *
     * @return string
     */
    public function __toString(): string
    {
        return trim($this->make . ' ' . $this->model);
    }

    /**
     * Improved var_dump() output
     *
     * @return array
     */
    public function __debuginfo(): array
    {
        return $this->toArray();
    }
}
