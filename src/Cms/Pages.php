<?php

namespace Kirby\Cms;

use Exception;
use Kirby\Cms\Pages\Finder;
use Kirby\Collection\Collection;

class Pages extends Collection
{
    protected function finder()
    {
        return new Finder($this);
    }

    public function indexOf($page)
    {
        return array_search($page->id(), $this->keys());
    }

    public function visible(): self
    {
        return $this->filterBy('isVisible', '==', true);
    }

    public function invisible(): self
    {
        return $this->filterBy('isVisible', '!=', true);
    }

    public function __set(string $id, $page)
    {

        if (is_array($page)) {
            $page = new Page($page);
        }

        if (!is_a($page, Page::class)) {
            throw new Exception('Invalid page object in pages collection');
        }

        // inject the collection for proper navigation
        $page->collection($this);

        return parent::__set($page->id(), $page);

    }

    public function getAttribute($item, $attribute)
    {
        return (string)$item->$attribute();
    }

}
