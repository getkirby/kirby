<?php

namespace Kirby\Panel\Options;

use Exception;
use Kirby\Cms\File;
use Kirby\Cms\Page;
use Kirby\Cms\Site;
use Kirby\Cms\Object;

class Query extends Object
{

    protected $attributes;

    public function __construct(array $props = [])
    {

        parent::__construct($props, [
            'site' => [
                'type'     => Site::class,
                'required' => true
            ],
            'page' => [
                'type' => 'string',
            ],
            'fetch' => [
                'type' => 'string',
            ],
            'template' => [
                'type' => 'string',
            ],
            'value' => [
                'type' => 'string',
            ],
            'text' => [
                'type' => 'string',
            ],
            'flip' => [
                'type' => 'boolean',
            ]
        ]);
    }

    public function getPage()
    {
        if ($this->page() === null || $this->page() === '/') {
            return $this->site();
        } elseif ($page = $this->site()->find($this->page())) {
            return $page;
        }

        throw new Exception(sprintf('The page "%s" could not be found', $this->page()));
    }

    public function collection()
    {
        $page  = $this->getPage();
        $fetch = $this->fetch() ?? 'children';

        if (method_exists($page, $fetch) === false &&
            $page->hasPlugin($fetch) === false &&
            $page->hasProp($fetch) === false) {
            throw new Exception(sprintf('Invalid fetch method: "%s"', $fetch));
        }

        $collection = $page->$fetch();

        if ($this->template()) {
            $collection = $collection->filterBy('template', $this->template());
        }

        return $collection;
    }

    public function item($item): array
    {
        $defaults = [
            Page::class => [
                'text'  => 'title',
                'value' => 'slug'
            ],
            File::class => [
                'text'  => 'filename',
                'value' => 'filename'
            ],
        ];

        $config = $defaults[get_class($item)] ?? [
            'text'  => 'id',
            'value' => 'id'
        ];

        $text  = $this->text()  ?? $config['text'];
        $value = $this->value() ?? $config['value'];

        return [
            'text'  => (string)$item->$text(),
            'value' => (string)$item->$value(),
        ];

    }

    public function toArray(): array
    {
        $output   = [];

        foreach ($this->collection() as $item) {
            $output[] = $this->item($item);
        }

        return $this->flip() ? array_reverse($output) : $output;
    }

}
