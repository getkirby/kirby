<?php

namespace Kirby\Cms;

use Closure;
use Exception;

use Kirby\Cms\File\Store;
use Kirby\Cms\File\Traits\Image;
use Kirby\Cms\File\Traits\Meta;
use Kirby\Cms\File\Traits\Mutator;
use Kirby\Cms\File\Traits\Navigator;
use Kirby\Cms\File\Traits\Relatives;
use Kirby\FileSystem\File as Asset;

class File
{

    use Image;
    use Meta;
    use Mutator;
    use Navigator;
    use Relatives;

    protected $attributes = [];
    protected $asset;
    protected $id;
    protected $root;
    protected $store;

    public function __construct($attributes)
    {
        $this->attributes = $attributes;

        if (empty($attributes['id'])) {
            throw new Exception('Please provide an ID for the file');
        }

        if (empty($attributes['url'])) {
            throw new Exception('Please provide a URL for the file');
        }

        if (empty($attributes['root'])) {
            throw new Exception('Please provide a root for the file');
        }

        // required attributes
        $this->id   = $attributes['id'];
        $this->url  = $attributes['url'];
        $this->root = $attributes['root'];

        // setup the asset
        $this->asset = new Asset($this->root);
        $this->store = new Store($this, $attributes);

    }

    public function id(): string
    {
        return $this->id;
    }

    public function root(): string
    {
        return $this->root;
    }

    public function url(): string
    {
        return $this->url;
    }

    public function is(File $file)
    {
        return $this->id() === $file->id();
    }

    public function __call($method, $arguments)
    {

        if (isset($this->attributes[$method]) === true) {
            return $this->attributes[$method];
        }

        if (method_exists($this->asset, $method) === true) {
            return $this->asset->{$method}(...$arguments);
        }

        return $this->meta()->get($method, $arguments);

    }

}
