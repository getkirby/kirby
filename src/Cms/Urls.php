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
class Urls extends Ingredients
{

    protected $api;
    protected $index;
    protected $media;
    protected $panel;

    public function api(): string
    {
        return $this->api = $this->api ?? rtrim($this->index(), '/') . '/api';
    }

    public function index(): string
    {
        return $this->index = $this->index ?? '/';
    }

    public function media(): string
    {
        return $this->media = $this->media ?? rtrim($this->index(), '/') . '/media';
    }

    public function panel(): string
    {
        return $this->panel = $this->panel ?? rtrim($this->index(), '/') . '/panel';
    }

}

