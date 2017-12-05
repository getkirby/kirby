<?php

namespace Kirby\Cms;

/**
 * Registry for all system-relevant Urls
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 */
class Urls extends Object
{

    /**
     * Creates the Urls registry.
     * Urls can be overwritten or added
     * with the $props variable.
     *
     * @param array $props
     */
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

    /**
     * Get a specific Url by key
     * If no key is specified the index Url
     * will be returned
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
     * Magic caller to fetch Urls by method call
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

