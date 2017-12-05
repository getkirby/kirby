<?php

namespace Kirby\Panel;

use Exception;
use Kirby\Cms\Object;
use Kirby\Cms\Page;
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

    public function collection()
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

    public function add()
    {
        // don't show the add button when there are already enough pages
        if ($this->max() !== null && $this->max() <= $this->total()) {
            return null;
        }

        // get the add options
        $options = $this->prop('add');

        // no button at all
        if (empty($options) === true) {
            return null;
        }

        return (new PagesSectionAdd($this, $options))->toArray();
    }

    public function toArray(): array
    {
        $pagination = $this->items()->pagination();
        $items      = $this->items()->toArray(function ($page) {

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
            'headline'   => $this->headline(),
            'items'      => array_values($items),
            'layout'     => $this->prop('layout'),
            'add'        => $this->add(),
            'pagination' => [
                'page'  => $pagination->page(),
                'limit' => $pagination->limit(),
                'total' => $pagination->total(),
            ]
        ];

    }

}
