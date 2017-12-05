<?php

namespace Kirby\Panel;

use Exception;
use Kirby\Cms\Field;
use Kirby\Cms\File;
use Kirby\Cms\Page;
use Kirby\Cms\Site;
use Kirby\Cms\Users;
use Kirby\Cms\Object;
use Kirby\Cms\Query;


class FieldOptionsSource extends Object
{

    protected $attributes;

    public function __construct(array $props = [])
    {

        parent::__construct($props, [
            'site' => [
                'type'     => Site::class,
                'default'  => function () {
                    return $this->plugin('kirby')->site();
                }
            ],
            'users' => [
                'type'     => Users::class,
                'default'  => function () {
                    return $this->plugin('kirby')->users();
                }
            ],
            'page' => [
                'type' => 'string',
            ],
            'file' => [
                'type' => 'string',
            ],
            'user' => [
                'type' => 'string',
            ],
            'query' => [
                'type' => 'string',
                'required' => true
            ],
            'value' => [
                'type' => 'string',
            ],
            'text' => [
                'type' => 'string',
            ],
            'flip' => [
                'type' => 'boolean',
            ]
        ]);

    }

    public function result()
    {
        $roots = [
            'site'  => $this->site(),
            'users' => $this->users()
        ];

        if ($this->page()) {
            $roots['page'] = $this->site()->find($this->page());
        }

        if ($this->file()) {
            $roots['file'] = $roots['page']->file($this->file());
        }

        if ($this->user()) {
            $roots['user'] = $this->users()->get($this->user());
        }

        return (new Query($this->query(), $roots))->result();
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

        return $this->flip() ? array_reverse($output) : $output;
    }

}
