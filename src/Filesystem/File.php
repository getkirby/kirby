<?php

namespace Kirby\Filesystem;

use Kirby\Cms\App;
use Kirby\Toolkit\File as BaseFile;
use Kirby\Toolkit\Html;
use Kirby\Toolkit\Properties;

/**
 * A representation of a file in the filesystem.
 * Extends the `Kirby\Toolkit\File` class with
 * Cms-specific properties and methods.
 *
 * @since 3.6.0
 *
 * @package   Kirby Filesystem
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
     * @param array|string|null $props Properties or deprecated `$root` string
     * @param string|null $url Deprecated argument, use `$props['url']` instead
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
     * Returns the file's last modification time
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
     * Converts the object to an array
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
     * Returns the URL for the file object
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->url() ?? '';
    }
}
