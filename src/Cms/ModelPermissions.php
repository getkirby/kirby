<?php

namespace Kirby\Cms;

abstract class ModelPermissions
{
    protected $category;
    protected $model;
    protected $options;
    protected $user;

    public function __call(string $method, array $arguments = [])
    {
        return $this->can($method);
    }

    public function __construct(Model $model)
    {
        $this->model       = $model;
        $this->options     = $model->blueprint()->options();
        $this->user        = $model->kirby()->user() ?? User::nobody();
        $this->permissions = $this->user->role()->permissions();
    }

    public function can(string $action): bool
    {
        if ($this->user->role()->id() === 'nobody') {
            return false;
        }

        if (method_exists($this, 'can' . $action) === true && $this->{'can' . $action}() === false) {
            return false;
        }

        if (array_key_exists($action, $this->options) === false || $this->options[$action] === false) {
            return false;
        }

        if (is_bool($this->options[$action]) === true) {
            return $this->options[$action];
        }

        return $this->permissions->for($this->category, $action);
    }

    public function cannot(string $action): bool
    {
        return $this->can() === false;
    }

    public function toArray(): array
    {
        $array = [];

        foreach ($this->options as $key => $value) {
            $array[$key] = $this->can($key);
        }

        return $array;
    }
}
