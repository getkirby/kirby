<?php

namespace Kirby\Cms;

use Kirby\Exception\BadMethodCallException;
use Kirby\Image\Image;
use Kirby\Toolkit\F;
use Kirby\Toolkit\Properties;

/**
 * Foundation for all file objects
 */
trait FileFoundation
{
    protected $asset;
    protected $root;
    protected $url;

    /**
     * Magic caller for asset methods
     *
     * @param string $method
     * @param array $arguments
     * @return mixed
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
     * Constructor sets all file properties
     *
     * @param array $props
     */
    public function __construct(array $props)
    {
        $this->setProperties($props);
    }

    /**
     * Converts the file object to a string
     * In case of an image, it will create an image tag
     * Otherwise it will return the url
     *
     * @return string
     */
    public function __toString(): string
    {
        if ($this->type() === 'image') {
            return $this->html();
        }

        return $this->url();
    }

    /**
     * Returns the Asset object
     *^
     * @return Image
     */
    public function asset(): Image
    {
        return $this->asset = $this->asset ?? new Image($this->root());
    }

    /**
     * Checks if the file exists on disk
     *
     * @return boolean
     */
    public function exists(): bool
    {
        return file_exists($this->root()) === true;
    }

    /**
     * Returns the file extension
     *
     * @return string
     */
    public function extension(): string
    {
        return F::extension($this->root());
    }

    /**
     * Converts the file to html
     *
     * @param  array  $attr
     * @return string
     */
    public function html(array $attr = []): string
    {
        if ($this->type() === 'image') {
            return Html::img($this->url(), array_merge(['alt' => $this->alt()], $attr));
        } else {
            return Html::a($this->url(), $attr);
        }
    }

    /**
     * Checks if the file is a resizable image
     *
     * @return boolean
     */
    public function isResizable(): bool
    {
        $resizable = [
            'jpg',
            'jpeg',
            'gif',
            'png',
            'webp'
        ];

        return in_array($this->extension(), $resizable) === true;
    }

    /**
     * Checks if a preview can be displayed for the file
     * in the panel or in the frontend
     *
     * @return boolean
     */
    public function isViewable(): bool
    {
        $viewable = [
            'jpg',
            'jpeg',
            'gif',
            'png',
            'svg',
            'webp'
        ];

        return in_array($this->extension(), $viewable) === true;
    }

    /**
     * Returns the paren app instance
     *
     * @return App
     */
    public function kirby(): App
    {
        return App::instance();
    }

    /**
     * Returns the absolute path to the file root
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
     * @param string $root
     * @return self
     */
    protected function setRoot(string $root = null)
    {
        $this->root = $root;
        return $this;
    }

    /**
     * Setter for the file url
     *
     * @param string $url
     * @return self
     */
    protected function setUrl(string $url)
    {
        $this->url = $url;
        return $this;
    }

    /**
     * Convert the object to an array
     *
     * @return array
     */
    public function toArray(): array
    {
        $array = array_merge($this->asset()->toArray(), [
            'isResizable' => $this->isResizable(),
            'url'         => $this->url(),
        ]);

        ksort($array);

        return $array;
    }

    /**
     * Returns the file type
     *
     * @return string|null
     */
    public function type()
    {
        return F::type($this->root());
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
