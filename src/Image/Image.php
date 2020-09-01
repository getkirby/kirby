<?php

namespace Kirby\Image;

use Exception;
use Kirby\Http\Response;
use Kirby\Toolkit\File;
use Kirby\Toolkit\Html;
use Kirby\Toolkit\I18n;
use Kirby\Toolkit\Mime;
use Kirby\Toolkit\V;

/**
 * A representation of an image/media file
 * with dimensions, optional exif data and
 * a connection to our darkroom classes to resize/crop
 * images.
 *
 * @package   Kirby Image
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier GmbH
 * @license   https://opensource.org/licenses/MIT
 */
class Image extends File
{
    /**
     * optional url where the file is reachable
     * @var string
     */
    protected $url;

    /**
     * @var \Kirby\Image\Exif|null
     */
    protected $exif;

    /**
     * @var \Kirby\Image\Dimensions|null
     */
    protected $dimensions;

    /**
     * Constructor
     *
     * @param string|null $root
     * @param string|null $url
     */
    public function __construct(string $root = null, string $url = null)
    {
        parent::__construct($root);
        $this->url = $url;
    }

    /**
     * Improved `var_dump` output
     *
     * @return array
     */
    public function __debugInfo(): array
    {
        return array_merge($this->toArray(), [
            'dimensions' => $this->dimensions(),
            'exif'       => $this->exif(),
        ]);
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
     * Returns the dimensions of the file if possible
     *
     * @return \Kirby\Image\Dimensions
     */
    public function dimensions()
    {
        if ($this->dimensions !== null) {
            return $this->dimensions;
        }

        if (in_array($this->mime(), ['image/jpeg', 'image/jp2', 'image/png', 'image/gif', 'image/webp'])) {
            return $this->dimensions = Dimensions::forImage($this->root);
        }

        if ($this->extension() === 'svg') {
            return $this->dimensions = Dimensions::forSvg($this->root);
        }

        return $this->dimensions = new Dimensions(0, 0);
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
        return Response::download($this->root, $filename ?? $this->filename());
    }

    /**
     * Returns the exif object for this file (if image)
     *
     * @return \Kirby\Image\Exif
     */
    public function exif()
    {
        if ($this->exif !== null) {
            return $this->exif;
        }
        $this->exif = new Exif($this);
        return $this->exif;
    }

    /**
     * Sends an appropriate header for the asset
     *
     * @param bool $send
     * @return \Kirby\Http\Response|string
     */
    public function header(bool $send = true)
    {
        $response = new Response('', $this->mime());
        return $send === true ? $response->send() : $response;
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
     * @param array $attr
     * @return string
     */
    public function html(array $attr = []): string
    {
        return Html::img($this->url(), $attr);
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
     * Checks if the dimensions of the asset are portrait
     *
     * @return bool
     */
    public function isPortrait(): bool
    {
        return $this->dimensions()->portrait();
    }

    /**
     * Checks if the dimensions of the asset are landscape
     *
     * @return bool
     */
    public function isLandscape(): bool
    {
        return $this->dimensions()->landscape();
    }

    /**
     * Checks if the dimensions of the asset are square
     *
     * @return bool
     */
    public function isSquare(): bool
    {
        return $this->dimensions()->square();
    }

    /**
     * Runs a set of validations on the image object
     *
     * @param array $rules
     * @return bool
     * @throws \Exception
     */
    public function match(array $rules): bool
    {
        if (($rules['mime'] ?? null) !== null) {
            if (Mime::isAccepted($this->mime(), $rules['mime']) !== true) {
                throw new Exception(I18n::template('error.file.mime.invalid', [
                    'mime' => $this->mime()
                ]));
            }
        }

        $rules = array_change_key_case($rules);

        $validations = [
            'maxsize'     => ['size',   'max'],
            'minsize'     => ['size',   'min'],
            'maxwidth'    => ['width',  'max'],
            'minwidth'    => ['width',  'min'],
            'maxheight'   => ['height', 'max'],
            'minheight'   => ['height', 'min'],
            'orientation' => ['orientation', 'same']
        ];

        foreach ($validations as $key => $arguments) {
            $rule = $rules[$key] ?? null;

            if ($rule !== null) {
                $property  = $arguments[0];
                $validator = $arguments[1];

                if (V::$validator($this->$property(), $rule) === false) {
                    throw new Exception(I18n::template('error.file.' . $key, [
                        $property => $rule
                    ]));
                }
            }
        }

        return true;
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
     * Converts the media object to a
     * plain PHP array
     *
     * @return array
     */
    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'dimensions' => $this->dimensions()->toArray(),
            'exif'       => $this->exif()->toArray(),
        ]);
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
     * Returns the url
     *
     * @return string
     */
    public function url()
    {
        return $this->url;
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
}
