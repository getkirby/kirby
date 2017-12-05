<?php

namespace Kirby\Panel;

use Exception;
use Kirby\Cms\Object;
use Kirby\Cms\Query;
use Kirby\Cms\Tempura;

class Section extends Object
{

    protected $kirby;
    protected $site;
    protected $model;

    public function __construct(array $props)
    {
        parent::__construct($props, $this->schema());

        $this->kirby = $this->plugin('kirby');
        $this->site  = $this->kirby->site();
        $this->model = $this->model();
    }

    public function schema(): array
    {
        return [];
    }

    public function query(string $query, array $data = [])
    {
        $defaults = [
            'site'  => $this->site,
            'kirby' => $this->kirby,
            'model' => $this->model,
        ];

        return (new Query($query, array_merge($defaults, $data)))->result();
    }

    public function template(string $template = null, array $data = [])
    {
        $defaults = [
            'site'  => $this->site,
            'kirby' => $this->kirby,
            'model' => $this->model,
        ];

        return (new Tempura($template, array_merge($defaults, $data)))->render();
    }

    public function model(): Object
    {
        if (is_a($this->prop('model'), Object::class) === true) {
            return $this->prop('model');
        }

        return (new Query($this->prop('model'), [
            'site'  => $this->site,
            'kirby' => $this->kirby
        ]))->result();
    }

}
