<?php

namespace Kirby\Cms;

/**
 * Registry for all system-relevant directory roots
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 */
class Roots extends Object
{

    /**
     * Creates a new root registry
     * Customize the folder structure
     * with the $props array
     *
     * @param array $props
     */
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

    /**
     * Returns a specific root from the registry
     *
     * @param  string $key
     * @return string|null
     */
    public function get(string $key = 'index')
    {
        if ($key === '/') {
            $key = 'index';
        }

        return $this->prop($key);
    }

    /**
     * Magic method getter for roots
     *
     * @param  string $method
     * @param  array $arguments
     * @return string|null
     */
    public function __call(string $method, array $arguments = [])
    {
        return $this->get($method);
    }

}

