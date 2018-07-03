<?php

namespace Kirby\Cms;

/**
 * Normalizes page options in page blueprints
 * and checks for each option, if the current
 * user is allowed to execute it.
 */
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
        'sort'           => null,
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

        return $this->isAllowed('pages', 'changeSlug');
    }

    public function changeStatus(): bool
    {
        if ($this->model()->isErrorPage() === true) {
            return false;
        }

        return $this->isAllowed('pages', 'changeStatus');
    }

    public function changeTitle(): bool
    {
        return $this->isAllowed('pages', 'changeTitle');
    }

    public function changeTemplate(): bool
    {
        if ($this->model()->isHomeOrErrorPage() === true) {
            return false;
        }

        if (count($this->model()->blueprints()) <= 1) {
            return false;
        }

        return $this->isAllowed('pages', 'changeTemplate');
    }

    public function create(): bool
    {
        return $this->isAllowed('pages', 'create');
    }

    public function delete(): bool
    {
        if ($this->model()->isHomeOrErrorPage() === true) {
            return false;
        }

        return $this->isAllowed('pages', 'delete');
    }

    public function preview(): bool
    {
        return $this->isAllowed('pages', 'preview');
    }

    public function read(): bool
    {
        return $this->isAllowed('pages', 'read');
    }

    public function sort(): bool
    {
        return $this->isAllowed('pages', 'sort');
    }

    public function update(): bool
    {
        return $this->isAllowed('pages', 'update');
    }
}
