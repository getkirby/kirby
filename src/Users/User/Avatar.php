<?php

namespace Kirby\Users\User;

use Kirby\FileSystem\File;
use Kirby\Object\Attributes;

class Avatar
{

    protected $asset;
    protected $attributes;

    public function __construct(array $attributes)
    {

        $this->attributes = Attributes::create($attributes, [
            'url' => [
                'type'     => 'string',
                'required' => true
            ],
            'root' => [
                'type'     => 'string',
                'required' => true
            ]
        ]);

        $this->asset = new File($this->attributes['root']);

    }

    public function url(): string
    {
        return $this->attributes['url'];
    }

    public function root(): string
    {
        return $this->attributes['root'];
    }

    public function __call($method, $arguments)
    {
        if (method_exists($this->asset, $method)) {
            return $this->asset->$method(...$arguments);
        }

        return null;
    }

}
