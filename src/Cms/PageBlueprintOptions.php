<?php

namespace Kirby\Cms;

use Exception;

class PageBlueprintOptions extends BlueprintOptions
{

    protected $aliases = [
        'status'   => 'changeStatus',
        'template' => 'changeTemplate',
        'title'    => 'changeTitle',
        'url'      => 'changeUrl',
    ];

    protected $options = [
        'changeStatus'   => true,
        'changeTitle'    => true,
        'changeTemplate' => true,
        'changeUrl'      => true,
        'delete'         => true,
        'preview'        => true,
        'read'           => true,
        'update'         => true,
    ];

    public function __construct(Page $model, array $options = null)
    {
        parent::__construct($model, $options);
    }

    public function changeStatus(): bool
    {
        if ($this->model->isErrorPage() === true) {
            return false;
        }

        return $this->options['changeStatus'];
    }

    public function changeTitle(): bool
    {
        return $this->options['changeTitle'];
    }

    public function changeTemplate(): bool
    {
        if ($this->model->isHomeOrErrorPage() === true) {
            return false;
        }

        return $this->options['changeTemplate'];
    }

    public function changeUrl(): bool
    {
        if ($this->model->isHomeOrErrorPage() === true) {
            return false;
        }

        return $this->options['changeUrl'];
    }

    public function delete(): bool
    {
        if ($this->model->isHomeOrErrorPage() === true) {
            return false;
        }

        return $this->options['delete'];
    }

    public function preview(): bool
    {
        return $this->options['preview'];
    }

    public function read(): bool
    {
        return $this->options['read'];
    }

    public function update(): bool
    {
        return $this->options['update'];
    }

}
