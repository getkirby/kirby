<?php

namespace Kirby\Panel\Options;

use Exception;
use Kirby\Cms\File;
use Kirby\Cms\Page;
use Kirby\Cms\Site;
use Kirby\Cms\Object;

class Field extends Object
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
            'field' => [
                'type'     => 'string',
                'required' => true
            ],
            'separator' => [
                'type' => 'string',
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
        $field = $this->field();

        if (empty($field) === true) {
            throw new Exception(sprintf('Invalid field name: "%s"', $field));
        }

        return $page->$field()->split($this->separator() ?? ',');
    }

    public function toArray(): array
    {
        return $this->collection();
    }

}
