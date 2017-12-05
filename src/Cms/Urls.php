<?php

namespace Kirby\Cms;

class Urls extends Object
{

    public function __construct(array $props = [])
    {

        parent::__construct($props, [
            'index' => [
                'type'    => 'string',
                'default' => function (): string {
                    return '/';
                }
            ],
            'media' => [
                'type'    => 'string',
                'default' => function (): string {
                    return rtrim($this->index(), '/') . '/media';
                }
            ],
            'panel' => [
                'type'    => 'string',
                'default' => function (): string {
                    return rtrim($this->index(), '/') . '/panel';
                }
            ],
            'api' => [
                'type'    => 'string',
                'default' => function (): string {
                    return rtrim($this->index(), '/') . '/api';
                }
            ]
        ]);

    }

    public function get(string $key = 'index')
    {
        if ($key === '/') {
            $key = 'index';
        }

        return $this->prop($key);
    }

    public function __call(string $method, array $arguments = [])
    {
        return $this->get($method);
    }

}

