<?php

namespace Kirby\Panel;

use Exception;
use Kirby\Cms\Collection;
use Kirby\Cms\Page;
use Kirby\Cms\Pages;
use Kirby\Cms\Site;

class CollectionSection extends Section
{

    protected $items;

    public function schema(): array
    {
        return [
            'self' => [
                'type'    => 'string',
                'default' => 'site'
            ],
            'layout' => [
                'type'     => 'string',
                'default'  => 'list',
                'validate' => function ($value) {
                    return in_array($value, ['cards', 'list']);
                }
            ],
            'title' => [
                'type' => 'string',
            ],
            'info' => [
                'type' => 'string',
            ],
            'filterBy' => [
                'type' => 'array'
            ],
            'sortBy' => [
                'type' => 'array'
            ],
            'min' => [
                'type' => 'integer'
            ],
            'max' => [
                'type' => 'integer'
            ]
        ];
    }

    public function items()
    {
        if (is_a($this->items, Collection::class) === true) {
            return $this->items;
        }

        // filter, sort, paginate
        return $this->items = $this->collection()->query([
            'filterBy' => $this->filterBy(),
            'sortBy'   => $this->sortBy(),
            'paginate' => $this->paginate(),
        ]);
    }

    public function total(): int
    {
        return $this->items()->pagination()->total();
    }

    public function self(): Object
    {
        $result = parent::self();

        if (
            is_a($result, Page::class) === true ||
            is_a($result, Site::class) === true
        ) {
            return $result;
        }

        return $this->site;
    }

    public function headline()
    {
        return $this->template($this->prop('headline'));
    }

    public function title($data = [])
    {
        return $this->template($this->prop('title'), $data);
    }

    public function info($data = [])
    {
        return $this->template($this->prop('info'), $data);
    }

    public function filterBy()
    {
        $filters = $this->prop('filterBy');

        if (empty($filters) === true) {
            return [];
        }

        foreach ($filters as $key => $filter) {
            $filters[$key]['value'] = $this->template($filter['value']);
        }

        return $filters;
    }

    public function sortBy()
    {
        return $this->prop('sortBy');
    }

    public function paginate(): array
    {

        $defaults = ['page' => 1, 'limit' => 20];
        $options  = $this->prop('paginate');

        if (is_int($options) === true) {
            return [
                'page'  => 1,
                'limit' => $options
            ];
        }

        if (is_array($options) === true) {
            return array_merge($defaults, $options);
        }

        return $defaults;

    }

}
