<?php

namespace Kirby\Image;

use Exception;
use Kirby\FileSystem\File;
use Kirby\Html\Element\Img;
use Kirby\Http\Response;
use Kirby\Http\Response\Download;
use Kirby\Http\Acceptance\MimeType;
use Kirby\Toolkit\Str;
use Kirby\Toolkit\V;

/**
 * Image
 *
 * @package   Kirby Image
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   MIT
*/
class Image extends File
{

    /**
     * optional url where the file is reachable
     * @var string
     */
    protected $url;

    /**
     * @var Exif|null
     */
    protected $exif;

    /**
     * @var Dimensions|null
     */
    protected $dimensions;

    /**
     * Constructor
     *
     * @param string       $root
     * @param string|null  $url
     */
    public function __construct(string $root = null, string $url = null)
    {
        parent::__construct($root);
        $this->url = $url;
    }

    /**
     * Returns the url
     *
     * @return string
     */
    public function url()
    {
        return $this->url;
    }

    /**
     * Returns a md5 hash of the root
     *
     * @return string
     */
    public function hash(): string
    {
        return md5($this->root);
    }

    /**
     * Returns the file content as base64 encoded string
     *
     * @return string
     */
    public function base64(): string
    {
        return base64_encode($this->read());
    }

    /**
     * Returns the file as data uri
     *
     * @return string
     */
    public function dataUri(): string
    {
        return 'data:' . $this->mime() . ';base64,' . $this->base64();
    }

    /**
     * Sends an appropriate header for the asset
     *
     * @param  boolean          $send
     * @return Response|string
     */
    public function header(bool $send = true)
    {
        $response = new Response();
        $response->type($this->mime());
        return $send === true ? $response->send() : $response;
    }


    /*
     * Automatically sends all needed headers for the file to be downloaded
     * and echos the file's content
     *
     * @param  string|null $filename  Optional filename for the download
     * @return string
     */
    public function download($filename = null): string
    {
        $download = new Download($this->root, $filename ?? $this->filename());
        return $download->send();
    }

    /**
     * Returns the exif object for this file (if image)
     *
     * @return Exif
     */
    public function exif(): Exif
    {
        if ($this->exif !== null) {
            return $this->exif;
        }
        $this->exif = new Exif($this);
        return $this->exif;
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
     * Returns the dimensions of the file if possible
     *
     * @return Dimensions
     */
    public function dimensions(): Dimensions
    {
        if ($this->dimensions !== null) {
            return $this->dimensions;
        }

        $width  = 0;
        $height = 0;

        if (in_array($this->mime(), ['image/jpeg', 'image/png', 'image/gif'])) {
            $size   = $this->imagesize();
            $width  = $size[0] ?? 0;
            $height = $size[1] ?? 0;
        } elseif ($this->extension() === 'svg') {
            $content = $this->read();
            $xml     = simplexml_load_string($content);
            if ($xml !== false) {
                $attr   = $xml->attributes();
                $width  = floatval($attr->width);
                $height = floatval($attr->height);
                if (($width === 0.0 || $height === 0.0) && empty($attr->viewBox) === false) {
                    $box    = Str::split($attr->viewBox, ' ');
                    $width  = floatval($box[2] ?? 0);
                    $height = floatval($box[3] ?? 0);
                }
            }
        }

        return $this->dimensions = new Dimensions($width, $height);
    }

    /**
     * Runs a set of validations on the image object
     *
     * @return bool
     */
    public function match(array $rules): bool
    {
        if (($rules['mime'] ?? null) !== null) {
            if ((new MimeType($rules['mime']))->has($this->mime()) === false) {
                throw new Exception('Invalid mime type');
            }
        }

        $rules = array_change_key_case($rules);

        $validations = [
            'maxsize'     => ['size',   'max', 'The file is too large'],
            'minsize'     => ['size',   'min', 'The file is too small'],
            'maxwidth'    => ['width',  'max', 'The width of the image is too large'],
            'minwidth'    => ['width',  'min', 'The width of the image is too small'],
            'maxheight'   => ['height', 'max', 'The height of the image is too large'],
            'minheight'   => ['height', 'min', 'The height of the image is too small'],
            'orientation' => ['orientation', 'same', 'The orientation of the image is incorrect']
        ];

        foreach ($validations as $key => $arguments) {
            if (isset($rules[$key]) === true && $rules[$key] !== null) {
                $property  = $arguments[0];
                $validator = $arguments[1];
                $message   = $arguments[2];

                if (V::$validator($this->$property(), $rules[$key]) === false) {
                    throw new Exception($message);
                }
            }
        }

        return true;
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
     * Returns the ratio of the asset
     *
     * @return float
     */
    public function ratio(): float
    {
        return $this->dimensions()->ratio();
    }

    /**
     * Checks if the dimensions of the asset are portrait
     *
     * @return boolean
     */
    public function isPortrait(): bool
    {
        return $this->dimensions()->portrait();
    }

    /**
     * Checks if the dimensions of the asset are landscape
     *
     * @return boolean
     */
    public function isLandscape(): bool
    {
        return $this->dimensions()->landscape();
    }

    /**
     * Checks if the dimensions of the asset are square
     *
     * @return boolean
     */
    public function isSquare(): bool
    {
        return $this->dimensions()->square();
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
     * @param  array  $attr
     * @return Img
     */
    public function html(array $attr = []): Img
    {
        if (isset($attr['alt']) === false) {
            $attr['alt'] = '';
        }
        $img = new Img($this->url(), $attr);
        return $img;
    }

    /**
     * Converts the media object to a
     * plain PHP array
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'root'         => $this->root(),
            'url'          => $this->url(),
            'hash'         => $this->hash(),
            'folder'       => $this->folder(),
            'filename'     => $this->filename(),
            'name'         => $this->name(),
            'safeName'     => File::safeName($this->name()),
            'extension'    => $this->extension(),
            'size'         => $this->size(),
            'niceSize'     => $this->niceSize(),
            'modified'     => $this->modified('c'),
            'mime'         => $this->mime(),
            'type'         => $this->type(),
            'dimensions'   => $this->dimensions()->toArray(),
            'isWritable'   => $this->isWritable(),
            'isReadable'   => $this->isReadable(),
            'header'       => $this->header(false),
        ];
    }

    /**
     * Converts the entire file array into
     * a json string
     *
     * @return string
     */
    public function toJson(): string
    {
        return json_encode($this->toArray());
    }

    /**
     * Returns a full link to this file
     * Perfect for debugging in connection with echo
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->root;
    }

    /**
     * Improved var_dump() output
     *
     * @return array
     */
    public function __debuginfo(): array
    {
        return array_merge($this->toArray(), [
            'dimensions' => $this->dimensions(),
            'exif'       => $this->exif(),
        ]);
    }
}
