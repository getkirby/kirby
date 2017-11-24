<?php

namespace Kirby\Panel\Sections;

use Kirby\Cms\Collection;
use Kirby\Cms\Object;
use Kirby\Cms\Pages;
use Kirby\Cms\Query;
use Kirby\Cms\Tempura;

class CollectionSection extends Object
{

    protected $kirby;
    protected $site;
    protected $self;
    protected $items;

    public function __construct(array $props)
    {
        parent::__construct($props, $this->schema());

        $this->kirby = $this->plugin('kirby');
        $this->site  = $this->kirby->site();
        $this->self  = $this->self();
    }

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
            ],
            'paginate' => [
                'type'    => 'array',
                'default' => [
                    'page'  => 1,
                    'limit' => 20
                ]
            ]
        ];
    }

    public function collection()
    {
        return new Collection;
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

    public function query(string $query, array $data = [])
    {
        $defaults = [
            'site'  => $this->site,
            'kirby' => $this->kirby,
            'self'  => $this->self,
        ];

        return (new Query($query, array_merge($defaults, $data)))->result();
    }

    public function total(): int
    {
        return $this->items()->pagination()->total();
    }

    public function template(string $template = null, array $data = [])
    {
        $defaults = [
            'site'  => $this->site,
            'kirby' => $this->kirby,
            'self'  => $this->self,
        ];

        return (new Tempura($template, array_merge($defaults, $data)))->render();
    }

    public function self(): Object
    {
        return (new Query($this->prop('self'), [
            'site'  => $this->site,
            'kirby' => $this->kirby
        ]))->result();
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
        return array_merge([
            'page'  => 1,
            'limit' => 20,
        ], $this->prop('paginate'));
    }

}
