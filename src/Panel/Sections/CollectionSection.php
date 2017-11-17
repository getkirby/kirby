<?php

namespace Kirby\Panel\Sections;

use Kirby\Cms\Object;
use Kirby\Cms\Pages;
use Kirby\Cms\Query;
use Kirby\Cms\Tempura;

class CollectionSection extends Object
{

    protected $kirby;
    protected $site;
    protected $self;

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
            'pagination' => [
                'type'    => 'array',
                'default' => [
                    'page'  => 1,
                    'limit' => 20
                ]
            ]
        ];
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

    public function template(string $template, array $data = [])
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

    public function pagination(): array
    {
        return array_merge([
            'page'  => 1,
            'limit' => 20,
        ], $this->prop('pagination'));
    }

}
