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
class Roots extends Ingredients
{

    protected $accounts;
    protected $blueprints;
    protected $collections;
    protected $content;
    protected $controllers;
    protected $index;
    protected $kirby;
    protected $loaders;
    protected $media;
    protected $panel;
    protected $plugins;
    protected $site;
    protected $snippets;
    protected $templates;

    public function accounts(): string
    {
        return $this->accounts = $this->accounts ?? $this->site() . '/accounts';
    }

    public function blueprints(): string
    {
        return $this->blueprints = $this->blueprints ?? $this->site() . '/blueprints';
    }

    public function collections(): string
    {
        return $this->collections = $this->collections ?? $this->site() . '/collections';
    }

    public function content(): string
    {
        return $this->content = $this->content ?? $this->index() . '/content';
    }

    public function controllers(): string
    {
        return $this->controllers = $this->controllers ?? $this->site() . '/controllers';
    }

    public function index(): string
    {
        return $this->index = $this->index ?? realpath(__DIR__ . '/../../../');
    }

    public function kirby(): string
    {
        return $this->kirby = $this->kirby ?? realpath(__DIR__ . '/../../');
    }

    public function loaders(): string
    {
        return $this->loaders = $this->loaders ?? $this->kirby() . '/loaders';
    }

    public function locales(): string
    {
        return $this->locales = $this->locales ?? $this->kirby() . '/locales';
    }

    public function media(): string
    {
        return $this->media = $this->media ?? $this->index() . '/media';
    }

    public function panel(): string
    {
        return $this->panel = $this->panel ?? $this->index() . '/panel';
    }

    public function plugins(): string
    {
        return $this->plugins = $this->plugins ?? $this->site() . '/plugins';
    }

    public function site(): string
    {
        return $this->site = $this->site ?? $this->index() . '/site';
    }

    public function snippets(): string
    {
        return $this->snippets = $this->snippets ?? $this->site() . '/snippets';
    }

    public function templates(): string
    {
        return $this->templates = $this->templates ?? $this->site() . '/templates';
    }

}

