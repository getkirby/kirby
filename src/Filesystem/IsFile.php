<?php

namespace Kirby\Filesystem;

use Kirby\Cms\App;
use Kirby\Exception\BadMethodCallException;
use Kirby\Image\Image;
use Kirby\Toolkit\Properties;

/**
 * Trait for all objects that represent an asset file.
 * Adds `::asset()` method which returns either a
 * `Kirby\Filesystem\File` or `Kirby\Image\Image` object.
 * Proxies method calls to this object.
 * @since 3.6.0
 *
 * @package   Kirby Filesystem
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
trait IsFile
{
    use Properties;

    /**
     * File asset object
     *
     * @var \Kirby\Filesystem\File
     */
    protected $asset;

    /**
     * Absolute file path
     *
     * @var string|null
     */
    protected $root;

    /**
     * Absolute file URL
     *
     * @var string|null
     */
    protected $url;

    /**
     * Constructor sets all file properties
     *
     * @param array $props
     */
    public function __construct(array $props)
    {
        $this->setProperties($props);
    }

    /**
     * Magic caller for asset methods
     *
     * @param string $method
     * @param array $arguments
     * @return mixed
     * @throws \Kirby\Exception\BadMethodCallException
     */
    public function __call(string $method, array $arguments = [])
    {
        // public property access
        if (isset($this->$method) === true) {
            return $this->$method;
        }

        // asset method proxy
        if (method_exists($this->asset(), $method)) {
            return $this->asset()->$method(...$arguments);
        }

        throw new BadMethodCallException('The method: "' . $method . '" does not exist');
    }

    /**
     * Converts the asset to a string
     *
     * @return string
     */
    public function __toString(): string
    {
        return (string)$this->asset();
    }

    /**
     * Returns the file asset object
     *
     * @param array|string|null $props
     * @return \Kirby\Filesystem\File
     */
    public function asset($props = null)
    {
        if ($this->asset !== null) {
            return $this->asset;
        }

        $props = $props ?? [
            'root' => $this->root(),
            'url'  => $this->url()
        ];

        switch ($this->type()) {
            case 'image':
                return $this->asset = new Image($props);
            default:
                return $this->asset = new File($props);
        }
    }

    /**
     * Checks if the file exists on disk
     *
     * @return bool
     */
    public function exists(): bool
    {
        // Important to include this in the trait
        // to avoid infinite loops when trying
        // to proxy the method from the asset object
        return file_exists($this->root()) === true;
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
     * Returns the given file path
     *
     * @return string|null
     */
    public function root(): ?string
    {
        return $this->root;
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
     * Returns the file type
     *
     * @return string|null
     */
    public function type(): ?string
    {
        // Important to include this in the trait
        // to avoid infinite loops when trying
        // to proxy the method from the asset object
        return F::type($this->root() ?? $this->url());
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
}
