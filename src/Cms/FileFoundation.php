<?php

namespace Kirby\Cms;

use Kirby\Exception\BadMethodCallException;
use Kirby\Image\Image;
use Kirby\Toolkit\Properties;

trait FileFoundation
{
    protected $asset;
    protected $root;
    protected $url;

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
        if ($this->isResizable() === true) {
            return $this->html();
        }

        return $this->url();
    }

    /**
     * Returns the Asset object
     *
     * @return Image
     */
    public function asset(): Image
    {
        return $this->asset = $this->asset ?? new Image($this->root());
    }

    public function exists(): bool
    {
        return file_exists($this->root()) === true;
    }

    public function extension(): string
    {
        return $this->asset()->extension();
    }

    /**
     * Converts the file to html
     *
     * @param  array  $attr
     * @return string
     */
    public function html(array $attr = []): string
    {
        if ($this->isResizable() === true) {
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

    public function kirby(): App
    {
        return App::instance();
    }

    public function root(): ?string
    {
        return $this->root;
    }

    protected function setRoot(string $root = null)
    {
        $this->root = $root;
    }

    protected function setUrl(string $url)
    {
        $this->url = $url;
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

    public function url(): string
    {
        return $this->url;
    }
}
