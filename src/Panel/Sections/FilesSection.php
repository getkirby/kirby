<?php

namespace Kirby\Panel\Sections;

use Kirby\Cms\Files;
use Kirby\Cms\Object;

class FilesSection extends CollectionSection
{

    public function schema(): array
    {
        return array_merge_recursive(parent::schema(), [
            'files' => [
                'type'    => 'string',
                'default' => 'self.files'
            ],
            'group' => [
                'type' => 'string'
            ],
            'title' => [
                'default' => '{{ file.filename }}'
            ],
            'info' => [
                'default' => '{{ file.niceSize }}'
            ]
        ]);
    }

    public function files(): Files
    {
        return $this->query($this->prop('files'));
    }

    public function image($file)
    {
        if (in_array($file->extension(), ['jpg', 'jpeg', 'gif', 'png', 'svg'])) {
            return [
                'url' => $file->url()
            ];
        }

        return null;
    }

    public function filterBy()
    {
        $filters = parent::filterBy();

        if ($group = $this->prop('group')) {
            $filters[] = [
                'field'    => 'group',
                'operator' => '==',
                'value'    => $group
            ];
        }

        return $filters;
    }

    public function toArray(): array
    {

        $data = $this->files()->query([
            'filterBy' => $this->filterBy(),
            'sortBy'   => $this->prop('sortBy'),
            'paginate' => $this->pagination(),
        ]);

        $items = $data->toArray(function ($file) {

            $data = ['file' => $file];

            return [
                'id'       => $file->id(),
                'text'     => $this->title($data),
                'info'     => $this->info($data),
                'image'    => $this->image($file),
                'link'     => $path = '/pages/' . $file->page()->id() . '/files/' . $file->filename(),
                'url'      => $file->url(),
                'parent'   => $file->page()->id(),
                'filename' => $file->filename(),
                'options'  => $this->kirby->url('api') . $path . '/options'
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
