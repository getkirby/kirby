<?php

namespace Kirby\Cms\Site;

use Kirby\Cms\Site;
use Kirby\Fields\Field;
use Kirby\Fields\Fields;
use Kirby\FileSystem\Folder;
use Kirby\Object\Attributes as BaseAttributes;

class Attributes extends BaseAttributes
{

    protected $url = '/';
    protected $root;
    protected $content;

    public function __construct(array $attributes = [])
    {
        $this->set($attributes);
    }

    protected function setUrl(string $url)
    {
        $this->url = $url;
    }

    public function getUrl()
    {
        return $this->url;
    }

    protected function setRoot(string $root)
    {
        $this->root = $root;
    }

    public function getRoot()
    {
        return $this->root;
    }

    protected function setContent(array $content)
    {
        $this->content = new Fields($content, function ($key, $value) {
            return new Field($key, $value);
        });
    }

    public function getContent(): Fields
    {
        if ($this->content === null) {
            $this->setContent([]);
        }

        return $this->content;
    }

    public function toArray()
    {
        return $this->pluck(
            'url',
            'root',
            'content'
        );
    }

}
