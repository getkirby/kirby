<?php

namespace Kirby\Panel\Options;

use Exception;
use Kirby\Cms\File;
use Kirby\Cms\Page;
use Kirby\Cms\Site;
use Kirby\Object\Attributes;

class Query
{

    protected $attributes;

    public function __construct(array $attributes)
    {
        $this->attributes = Attributes::create($attributes, [
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
            'value' => [
                'type' => 'string',
            ],
            'text' => [
                'type' => 'string',
            ]
        ]);
    }

    public function site(): Site
    {
        return $this->attributes['site'];
    }

    public function page()
    {
        if ($this->attributes['page'] === null || $this->attributes['page'] === '/') {
            return $this->site();
        } elseif ($page = $this->site()->find($this->attributes['page'])) {
            return $page;
        }

        throw new Exception(sprintf('The page "%s" could not be found', $this->attributes['page']));
    }

    public function collection()
    {
        $page  = $this->page();
        $fetch = $this->attributes['fetch'] ?? 'children';

        if (method_exists($page, $fetch) === false) {
            throw new Exception(sprintf('Invalid fetch method: "%s"', $fetch));
        }

        return $page->$fetch();
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

        $text  = $this->attributes['text']  ?? $config['text'];
        $value = $this->attributes['value'] ?? $config['value'];

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

        return $output;
    }

}
