<?php

namespace Kirby\Image;

use Kirby\Toolkit\V;

/**
 * Reads exif data from a given image object
 *
 * @package   Kirby Image
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier GmbH
 * @license   https://opensource.org/licenses/MIT
 */
class Exif
{
    /**
     * the parent image object
     * @var Image
     */
    protected $image;

    /**
     * the raw exif array
     * @var array
     */
    protected $data = [];

    /**
     * the camera object with model and make
     * @var Camera
     */
    protected $camera;

    /**
     * the location object
     * @var Location
     */
    protected $location;

    /**
     * the timestamp
     *
     * @var string
     */
    protected $timestamp;

    /**
     * the exposure value
     *
     * @var string
     */
    protected $exposure;

    /**
     * the aperture value
     *
     * @var string
     */
    protected $aperture;

    /**
     * iso value
     *
     * @var string
     */
    protected $iso;

    /**
     * focal length
     *
     * @var string
     */
    protected $focalLength;

    /**
     * color or black/white
     * @var bool
     */
    protected $isColor;

    /**
     * Constructor
     *
     * @param \Kirby\Image\Image $image
     */
    public function __construct(Image $image)
    {
        $this->image = $image;
        $this->data  = $this->read();
        $this->parse();
    }

    /**
     * Returns the raw data array from the parser
     *
     * @return array
     */
    public function data(): array
    {
        return $this->data;
    }

    /**
     * Returns the Camera object
     *
     * @return \Kirby\Image\Camera|null
     */
    public function camera()
    {
        if ($this->camera !== null) {
            return $this->camera;
        }

        return $this->camera = new Camera($this->data);
    }

    /**
     * Returns the location object
     *
     * @return \Kirby\Image\Location|null
     */
    public function location()
    {
        if ($this->location !== null) {
            return $this->location;
        }

        return $this->location = new Location($this->data);
    }

    /**
     * Returns the timestamp
     *
     * @return string|null
     */
    public function timestamp()
    {
        return $this->timestamp;
    }

    /**
     * Returns the exposure
     *
     * @return string|null
     */
    public function exposure()
    {
        return $this->exposure;
    }

    /**
     * Returns the aperture
     *
     * @return string|null
     */
    public function aperture()
    {
        return $this->aperture;
    }

    /**
     * Returns the iso value
     *
     * @return int|null
     */
    public function iso()
    {
        return $this->iso;
    }

    /**
     * Checks if this is a color picture
     *
     * @return bool|null
     */
    public function isColor()
    {
        return $this->isColor;
    }

    /**
     * Checks if this is a bw picture
     *
     * @return bool|null
     */
    public function isBW(): ?bool
    {
        return ($this->isColor !== null) ? $this->isColor === false : null;
    }

    /**
     * Returns the focal length
     *
     * @return string|null
     */
    public function focalLength()
    {
        return $this->focalLength;
    }

    /**
     * Read the exif data of the image object if possible
     *
     * @return mixed
     */
    protected function read(): array
    {
        if (function_exists('exif_read_data') === false) {
            return [];
        }

        $data = @exif_read_data($this->image->root());
        return is_array($data) ? $data : [];
    }

    /**
     * Get all computed data
     *
     * @return array
     */
    protected function computed(): array
    {
        return $this->data['COMPUTED'] ?? [];
    }

    /**
     * Pareses and stores all relevant exif data
     */
    protected function parse()
    {
        $this->timestamp   = $this->parseTimestamp();
        $this->exposure    = $this->data['ExposureTime'] ?? null;
        $this->iso         = $this->data['ISOSpeedRatings'] ?? null;
        $this->focalLength = $this->parseFocalLength();
        $this->aperture    = $this->computed()['ApertureFNumber'] ?? null;
        $this->isColor     = V::accepted($this->computed()['IsColor'] ?? null);
    }

    /**
     * Return the timestamp when the picture has been taken
     *
     * @return string|int
     */
    protected function parseTimestamp()
    {
        if (isset($this->data['DateTimeOriginal']) === true) {
            return strtotime($this->data['DateTimeOriginal']);
        }

        return $this->data['FileDateTime'] ?? $this->image->modified();
    }

    /**
     * Teturn the focal length
     *
     * @return string|null
     */
    protected function parseFocalLength()
    {
        return $this->data['FocalLength'] ?? $this->data['FocalLengthIn35mmFilm'] ?? null;
    }

    /**
     * Converts the object into a nicely readable array
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'camera'      => $this->camera() ? $this->camera()->toArray() : null,
            'location'    => $this->location() ? $this->location()->toArray() : null,
            'timestamp'   => $this->timestamp(),
            'exposure'    => $this->exposure(),
            'aperture'    => $this->aperture(),
            'iso'         => $this->iso(),
            'focalLength' => $this->focalLength(),
            'isColor'     => $this->isColor()
        ];
    }

    /**
     * Improved `var_dump` output
     *
     * @return array
     */
    public function __debugInfo(): array
    {
        return array_merge($this->toArray(), [
            'camera'   => $this->camera(),
            'location' => $this->location()
        ]);
    }
}
