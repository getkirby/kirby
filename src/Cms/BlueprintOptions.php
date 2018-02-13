<?php

namespace Kirby\Cms;

class BlueprintOptions
{
    protected $model;
    protected $options = [];

    public function __construct(Model $model, array $options = null)
    {
        $this->model = $model;

        if ($options !== null) {
            foreach ($this->options as $key => $default) {
                if (isset($options[$key]) === true) {
                    $this->options[$key] = (bool)$options[$key];
                }
            }
        }

    }

    public function kirby()
    {
        return $this->model()->kirby();
    }

    public function model()
    {
        return $this->model;
    }

    public function toArray(): array
    {
        $result = [];

        foreach ($this->options as $key => $value) {
            $result[$key] = $this->$key();
        }

        return $result;
    }

}
