<?php

namespace Kirby\Cms;

class Roots extends Object
{

    public function __construct(array $props = [])
    {

        parent::__construct($props, [
            'index' => [
                'type'    => 'string',
                'default' => function (): string {
                    return realpath(__DIR__ . '/../../../');
                }
            ],
            'kirby' => [
                'type'    => 'string',
                'default' => function (): string {
                    return $this->index() . '/kirby';
                }
            ],
            'media' => [
                'type'    => 'string',
                'default' => function (): string {
                    return $this->index() . '/media';
                }
            ],
            'content' => [
                'type'    => 'string',
                'default' => function (): string {
                    return $this->index() . '/content';
                }
            ],
            'site' => [
                'type'    => 'string',
                'default' => function (): string {
                    return $this->index() . '/site';
                }
            ],
            'controllers' => [
                'type'    => 'string',
                'default' => function (): string {
                    return $this->site() . '/controllers';
                }
            ],
            'accounts' => [
                'type'    => 'string',
                'default' => function (): string {
                    return $this->site() . '/accounts';
                }
            ],
            'snippets' => [
                'type'    => 'string',
                'default' => function (): string {
                    return $this->site() . '/snippets';
                }
            ],
            'templates' => [
                'type'    => 'string',
                'default' => function (): string {
                    return $this->site() . '/templates';
                }
            ],
            'plugins' => [
                'type'    => 'string',
                'default' => function (): string {
                    return $this->site() . '/plugins';
                }
            ],
            'blueprints' => [
                'type'    => 'string',
                'default' => function (): string {
                    return $this->site() . '/blueprints';
                }
            ],
            'panel' => [
                'type'    => 'string',
                'default' => function (): string {
                    return $this->index() . '/panel';
                }
            ],
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

