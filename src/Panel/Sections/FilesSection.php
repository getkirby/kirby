<?php

namespace Kirby\Panel\Sections;

use Kirby\Cms\Files;
use Kirby\Cms\Object;
use Kirby\Panel\Sections\FilesSection\Add;

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

    public function collection()
    {
        return $this->query($this->prop('files'));
    }

    public function group()
    {
        return $this->prop('group');
    }

    public function image($file)
    {
        if (in_array($file->extension(), ['jpg', 'jpeg', 'gif', 'png', 'svg'])) {
            return [
                'url' => $file->url() . '?v=' . $file->modified()
            ];
        }

        return null;
    }

    public function filterBy()
    {
        $filters = parent::filterBy();

        if ($group = $this->group()) {
            $filters[] = [
                'field'    => 'group',
                'operator' => '==',
                'value'    => $group
            ];
        }

        return $filters;
    }

    public function add()
    {
        // don't show the add button when there are already enough files
        if ($this->max() !== null && $this->max() <= $this->total()) {
            return null;
        }

        // get the add options
        $options = $this->prop('add');

        // no button at all
        if (empty($options) === true) {
            return null;
        }

        return (new Add($this, $options))->toArray();
    }

    public function toArray(): array
    {

        $pagination = $this->items()->pagination();
        $items      = $this->items()->toArray(function ($file) {

            $data = ['file' => $file];

            return [
                'id'       => $file->id(),
                'text'     => $this->title($data),
                'info'     => $this->info($data),
                'image'    => $this->image($file),
                'link'     => $path = '/pages/' . $file->page()->id() . '/files/' . $file->filename(),
                'url'      => $file->url() ,
                'parent'   => $file->page()->id(),
                'mime'     => $file->mime(),
                'filename' => $file->filename(),
                'options'  => $this->kirby->url('api') . $path . '/options'
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
