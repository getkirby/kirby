<?php

namespace Kirby\Panel\Options;

use Exception;
use Kirby\Cms\Field;
use Kirby\Cms\File;
use Kirby\Cms\Page;
use Kirby\Cms\Site;
use Kirby\Cms\Users;
use Kirby\Cms\Object;
use Kirby\Cms\Query;


class Source extends Object
{

    protected $attributes;

    public function __construct(array $props = [])
    {

        parent::__construct($props, [
            'site' => [
                'type'     => Site::class,
                'required' => true
            ],
            'users' => [
                'type'     => Users::class,
                'required' => true
            ],
            'model' => [
                'type'     => 'string',
                'required' => true
            ],
            'path' => [
                'type'     => 'string',
                'required' => true
            ],
            'query' => [
                'type' => 'string'
            ],
            'value' => [
                'type' => 'string',
            ],
            'text' => [
                'type' => 'string',
            ]
        ]);

    }

    public function result()
    {
        $entries = [
            'site'  => $this->site(),
            'users' => $this->users()
        ];

        if ($this->model() === 'Page') {
            $entries['page'] = $this->site()->find($this->path());
        }

        if ($this->model() === 'File') {
            // $entries['user'] = $this->users()->find($this->path());
        }

        if ($this->model() === 'User') {
            $entries['user'] = $this->users()->get($this->path());
        }

        return (new Query($this->query() ?? 'page.children', $entries))->result();
    }

    public function item($item): array
    {

        if (is_string($item) === true) {
            return [
                'text'  => $item,
                'value' => $item,
            ];
        }

        $defaults = [
            Page::class => [
                'text'  => 'title',
                'value' => 'slug'
            ],
            File::class => [
                'text'  => 'filename',
                'value' => 'filename'
            ]
        ];

        $config = $defaults[get_class($item)] ?? [
            'text'  => 'id',
            'value' => 'id'
        ];

        $text  = $this->text()  ?? $config['text'];
        $value = $this->value() ?? $config['value'];

        return [
            'text'  => (string)$item->$text(),
            'value' => (string)$item->$value(),
        ];

    }

    public function toArray(): array
    {
        $result = $this->result();
        $output = [];

        if (is_a($result, Field::class) === true) {
            $result = $result->split();
        }

        foreach ($result as $item) {
            $output[] = $this->item($item);
        }

        return $output;
    }

}
