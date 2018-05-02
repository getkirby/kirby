<?php

namespace Kirby\Cms;

class PageBlueprintOptions extends BlueprintOptions
{
    protected $aliases = [
        'status'   => 'changeStatus',
        'template' => 'changeTemplate',
        'title'    => 'changeTitle',
        'url'      => 'changeSlug',
    ];

    protected $options = [
        'changeSlug'     => null,
        'changeStatus'   => null,
        'changeTemplate' => null,
        'changeTitle'    => null,
        'create'         => null,
        'delete'         => null,
        'preview'        => null,
        'update'         => null,
    ];

    public function __construct(Page $model, array $options = null)
    {
        parent::__construct($model, $options);
    }

    public function changeSlug(): bool
    {
        if ($this->model()->isHomeOrErrorPage() === true) {
            return false;
        }

        return $this->isAllowed('page', 'changeSlug');
    }

    public function changeStatus(): bool
    {
        if ($this->model()->isErrorPage() === true) {
            return false;
        }

        return $this->isAllowed('page', 'changeStatus');
    }

    public function changeTitle(): bool
    {
        return $this->isAllowed('page', 'changeTitle');
    }

    public function changeTemplate(): bool
    {
        if ($this->model()->isHomeOrErrorPage() === true) {
            return false;
        }

        if (count($this->model()->blueprints()) <= 1) {
            return false;
        }

        return $this->isAllowed('page', 'changeTemplate');
    }

    public function create(): bool
    {
        return $this->isAllowed('page', 'create');
    }

    public function delete(): bool
    {
        if ($this->model()->isHomeOrErrorPage() === true) {
            return false;
        }

        return $this->isAllowed('page', 'delete');
    }

    public function preview(): bool
    {
        return $this->isAllowed('page', 'preview');
    }

    public function read(): bool
    {
        return $this->isAllowed('page', 'read');
    }

    public function update(): bool
    {
        return $this->isAllowed('page', 'update');
    }
}
