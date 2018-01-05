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
     * Property Schema
     *
     * @return array
     */
    protected function schema()
    {
        return [
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
        ];
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

        return $this->props->get($key);
    }

}

