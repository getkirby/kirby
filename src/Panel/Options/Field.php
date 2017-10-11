<?php

namespace Kirby\Panel\Options;

use Exception;
use Kirby\Cms\File;
use Kirby\Cms\Page;
use Kirby\Cms\Site;
use Kirby\Object\Attributes;

class Field
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
            'field' => [
                'type'     => 'string',
                'required' => true
            ],
            'separator' => [
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
        $field = $this->attributes['field'];

        if (empty($field)) {
            throw new Exception(sprintf('Invalid field name: "%s"', $field));
        }

        return $page->$field()->split($this->attributes['separator'] ?? ',');
    }

    public function toArray(): array
    {
        return $this->collection();
    }

}
