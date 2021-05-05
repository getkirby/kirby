<?php

namespace Kirby\File;

use Kirby\Cms\App;
use Kirby\Exception\Exception;
use Kirby\Toolkit\File as BaseFile;
use Kirby\Toolkit\Html;
use Kirby\Toolkit\Mime;
use Kirby\Toolkit\Properties;
use Kirby\Toolkit\V;

/**
 * A representation of an file in the filesystem.
 * Extends the `Kirby\Toolkit\File` class with
 * Cms-specific properties and methods.
 *
 * @since 3.6.0
 *
 * @package   Kirby File
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier GmbH
 * @license   https://getkirby.com/license
 */
class File extends BaseFile
{
    use Properties;

    /**
     * Absolute file URL
     *
     * @var string|null
     */
    protected $url;

    /**
     * Constructor sets all file properties
     *
     * @param string|array|null $props
     * @param string|null $url
     */
    public function __construct($props = null, string $url = null)
    {
        // Legacy support for old constructor of
        // the `Kirby\Image\Image` class
        // @todo 4.0.0 remove
        if (is_array($props) === false) {
            $props = [
                'root' => $props,
                'url'  => $url
            ];
        }

        $this->setProperties($props);
    }

    /**
     * Converts the file to html
     *
     * @param array $attr
     * @return string
     */
    public function html(array $attr = []): string
    {
        return Html::a($this->url(), $attr);
    }

    /**
     * Checks if the file is a resizable image
     *
     * @return bool
     */
    public function isResizable(): bool
    {
        return false;
    }

    /**
     * Checks if a preview can be displayed for the file
     * in the panel or in the frontend
     *
     * @return bool
     */
    public function isViewable(): bool
    {
        return false;
    }

    /**
     * Returns the app instance
     *
     * @return \Kirby\Cms\App
     */
    public function kirby()
    {
        return App::instance();
    }

    /**
     * Runs a set of validations on the file object
     * (mainly for images).
     *
     * @param array $rules
     * @return bool
     * @throws \Kirby\Exception\Exception
     */
    public function match(array $rules): bool
    {
        $rules = array_change_key_case($rules);


        if (is_array($rules['mime'] ?? null) === true) {
            $mime = $this->mime();

            // determine if any pattern matches the MIME type;
            // once any pattern matches, `$carry` is `true` and the rest is skipped
            $matches = array_reduce($rules['mime'], function ($carry, $pattern) use ($mime) {
                return $carry || Mime::matches($mime, $pattern);
            }, false);

            if ($matches !== true) {
                throw new Exception([
                    'key'  => 'file.mime.invalid',
                    'data' => compact('mime')
                ]);
            }
        }

        if (is_array($rules['extension'] ?? null) === true) {
            $extension = $this->extension();
            if (in_array($extension, $rules['extension']) !== true) {
                throw new Exception([
                    'key'  => 'file.extension.invalid',
                    'data' => compact('extension')
                ]);
            }
        }

        if (is_array($rules['type'] ?? null) === true) {
            $type = $this->type();
            if (in_array($type, $rules['type']) !== true) {
                throw new Exception([
                    'key'  => 'file.type.invalid',
                    'data' => compact('type')
                ]);
            }
        }

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
                    throw new Exception([
                        'key'  => 'file.' . $key,
                        'data' => [$property => $rule]
                    ]);
                }
            }
        }

        return true;
    }

    /**
     * Get the file's last modification time.
     *
     * @param string $format
     * @param string|null $handler date or strftime
     * @return mixed
     */
    public function modified(string $format = null, string $handler = null)
    {
        return parent::modified(
            $format,
            $handler ?? $this->kirby()->option('date.handler', 'date')
        );
    }

    /**
     * Setter for the root
     *
     * @param string|null $root
     * @return $this
     */
    protected function setRoot(?string $root = null)
    {
        $this->root = $root;
        return $this;
    }

    /**
     * Setter for the file url
     *
     * @param string|null $url
     * @return $this
     */
    protected function setUrl(?string $url = null)
    {
        $this->url = $url;
        return $this;
    }

    /**
     * Returns the absolute url for the file
     *
     * @return string|null
     */
    public function url(): ?string
    {
        return $this->url;
    }

    /**
     * Convert the object to an array
     *
     * @return array
     */
    public function toArray(): array
    {
        $array = array_merge(parent::toArray(), [
            'isResizable' => $this->isResizable(),
            'url'         => $this->url()
        ]);

        ksort($array);

        return $array;
    }


    /**
     * Return the url for the file object
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->url();
    }
}
