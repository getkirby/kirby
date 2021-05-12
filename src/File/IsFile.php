<?php

namespace Kirby\File;

use Kirby\Cms\App;
use Kirby\Exception\BadMethodCallException;
use Kirby\Image\Image;
use Kirby\Toolkit\F;
use Kirby\Toolkit\Properties;

/**
 * Trait for all objects that represent an asset file.
 * Adds `::asset()` method which returns either a
 * `Kirby\File\File` or `Kirby\Image\Image` object.
 * Proxies method calls to this object.
 * @since 3.6.0
 *
 * @package   Kirby File
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier GmbH
 * @license   https://getkirby.com/license
 */
trait IsFile
{
    use Properties;

    /**
     * File asset object
     *
     * @var \Kirby\File\File
     */
    protected $asset;

    /**
     * Absolute file path
     *
     * @var string
     */
    protected $root;


    /**
     * Absolute file URL
     *
     * @var string
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
        // Public property access
        if (isset($this->$method) === true) {
            return $this->$method;
        }

        // Asset method proxy
        if (method_exists($this->asset(), $method)) {
            return $this->asset()->$method(...$arguments);
        }

        throw new BadMethodCallException('The method: "' . $method . '" does not exist');
    }

    /**
     * Returns the file asset object
     *
     * @return \Kirby\File\File
     */
    public function asset()
    {
        if ($this->asset !== null) {
            return $this->asset;
        }

        $props = [
            'root' => $this->root(),
            'url'  => $this->url()
        ];

        if (F::type($this->root() ?? $this->url()) === 'image') {
            return $this->asset = new Image($props);
        }

        return $this->asset = new File($props);
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
     * Returns the absolute url for the file
     *
     * @return string
     */
    public function url(): string
    {
        return $this->url;
    }
}
