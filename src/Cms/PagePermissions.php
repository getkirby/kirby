<?php

namespace Kirby\Cms;

class PagePermissions extends ModelPermissions
{
    protected $category = 'pages';

    protected function canChangeSlug(): bool
    {
        return $this->model->isHomeOrErrorPage() !== true;
    }

    protected function canChangeStatus(): bool
    {
        return $this->model->isErrorPage() !== true;
    }

    protected function canChangeTemplate(): bool
    {
        if ($this->model->isHomeOrErrorPage() === true) {
            return false;
        }

        if (count($this->model->blueprints()) <= 1) {
            return false;
        }

        return true;
    }

    protected function canDelete(): bool
    {
        return $this->model->isHomeOrErrorPage() !== true;
    }
}
