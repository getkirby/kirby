<?php

namespace Kirby\Form\Mixins;

trait Model
{

    protected $model;

    protected function defaultModel()
    {
        return null;
    }

    public function model()
    {
        return $this->model;
    }

    /**
     * Set the parent model
     *
     * @param Page|Site|User|File $model
     * @return self
     */
    protected function setModel($model = null)
    {
        $this->model = $model;
        return $this;
    }

}
