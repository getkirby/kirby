<?php

namespace Kirby\Cms;

trait HasModel
{
    protected $model;

    /**
     * Returns the parent model
     *
     * @return Page|File|Site|User
     */
    public function model()
    {
        return $this->model;
    }

    /**
     * Sets the parent model
     *
     * @param Page|File|User|Site $model
     * @return self
     */
    protected function setModel($model = null)
    {
        $this->model = $model;
        return $this;
    }
}
