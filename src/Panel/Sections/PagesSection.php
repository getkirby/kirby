<?php

namespace Kirby\Panel\Sections;

use Kirby\Cms\Object;
use Kirby\Cms\Pages;

class PagesSection extends CollectionSection
{

    public function schema(): array
    {
        return array_merge_recursive(parent::schema(), [
            'pages' => [
                'type'    => 'string',
                'default' => 'self.children'
            ],
            'template' => [
                'type' => 'string'
            ],
            'image' => [
                'default' => 'page.image'
            ],
            'title' => [
                'default' => '{{ page.title }}'
            ],
            'info' => [
                'default' => ''
            ],
        ]);
    }

    public function pages(): Pages
    {
        return $this->query($this->prop('pages'));
    }

    public function image(array $data)
    {
        if ($image = $this->query($this->prop('image'), $data)) {
            return [
                'url' => $image->url()
            ];
        }

        return null;
    }

    public function filterBy()
    {

        $filters = parent::filterBy();

        if ($template = $this->prop('template')) {
            $filters[] = [
                'field'    => 'template',
                'operator' => '==',
                'value'    => $template
            ];
        }

        return $filters;

    }

    public function toArray(): array
    {

        $data = $this->pages()->query([
            'filterBy' => $this->filterBy(),
            'sortBy'   => $this->prop('sortBy'),
            'paginate' => $this->pagination(),
        ]);

        $items = $data->toArray(function ($page) {
            $data = ['page' => $page];

            return [
                'id'      => $page->id(),
                'text'    => $this->title($data),
                'info'    => $this->info($data),
                'image'   => $this->image($data),
                'link'    => '/pages/' . $page->id(),
                'options' => $this->kirby->url('api') . '/pages/' . $page->id() . '/options'
            ];
        });

        return [
            'items'      => array_values($items),
            'layout'     => $this->prop('layout'),
            'pagination' => [
                'page'  => $data->pagination()->page(),
                'limit' => $data->pagination()->limit(),
                'total' => $data->pagination()->total(),
            ]
        ];

    }

}
