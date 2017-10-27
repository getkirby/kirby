<?php

namespace Kirby\Cms;

class StructureObject extends Object
{

    use HasSiblings;

    public function __construct(array $props = [])
    {
        parent::__construct($props, [
            'id' => [
                'type'     => 'string',
                'required' => true,
            ],
            'content' => [
                'type'    => Content::class,
                'default' => function () {
                    return new Content([], $this->parent());
                }
            ],
            'parent' => [
                'type' => Object::class,
            ],
            'collection' => [
                'type' => Structure::class
            ]
        ]);
    }

    public function __call($method, $arguments)
    {

        if ($this->hasPlugin($method)) {
            return $this->plugin($method);
        }

        if ($this->hasProp($method)) {
            return $this->prop($method);
        }

        if (isset($this->dependencies[$method])) {
            return $this->dependencies[$method];
        }

        return $this->prop('content')->get($method, ...$arguments);

    }

    public function toArray(): array
    {
        return $this->prop('content')->toArray();
    }

}
