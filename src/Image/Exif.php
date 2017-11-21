<?php

namespace Kirby\Image;

use Kirby\Image\Exif\Camera;
use Kirby\Image\Exif\Location;
use Kirby\Toolkit\A;
use Kirby\Toolkit\V;

/**
 * Exif - Reads exif data from a given media object
 *
 * @package   Kirby Image
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   MIT
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
     * @var Exif\Camera
     */
    protected $camera;

    /**
     * the location object
     * @var Exif\Location
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
     * @param Image $image
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
     * @return Camera|null
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
     * @return Location|null
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
     * @return boolean|null
     */
    public function isColor()
    {
        return $this->isColor;
    }

    /**
     * Checks if this is a bw picture
     *
     * @return boolean|null
     */
    public function isBW(): bool
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
        $data = @read_exif_data($this->image->root());
        return is_array($data) ? $data : [];
    }

    /**
     * Pareses and stores all relevant exif data
     */
    protected function parse()
    {
        $this->timestamp   = $this->parseTimestamp();
        $this->exposure    = A::get($this->data, 'ExposureTime');
        $this->iso         = A::get($this->data, 'ISOSpeedRatings');
        $this->focalLength = $this->parseFocalLength();
        $this->aperture    = @$this->data['COMPUTED']['ApertureFNumber'];
        $this->isColor     = V::accepted(@$this->data['COMPUTED']['IsColor']);
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

        return A::get($this->data, 'FileDateTime', $this->image->modified());
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
     * Improved var_dump() output
     *
     * @return array
     */
    public function __debuginfo(): array
    {
        return array_merge($this->toArray(), [
            'camera'   => $this->camera(),
            'location' => $this->location()
        ]);
    }
}
