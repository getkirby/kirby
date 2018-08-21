<?php

namespace Kirby\Cms;

/**
 * Basic representation of the `options` setting
 * in blueprints. Each model (Page, File, Site)
 * has their own specific implementation of this.
 */
class BlueprintOptions
{
    protected $model;
    protected $aliases = [];
    protected $options = [];

    public function __construct(Model $model, array $options = null)
    {
        $this->model = $model;

        if ($options !== null) {
            foreach ($options as $key => $value) {
                if (isset($this->aliases[$key]) === true) {
                    $options[$this->aliases[$key]] = $value;
                    unset($options[$key]);
                }
            }

            foreach ($this->options as $key => $default) {
                if (isset($options[$key]) === true) {
                    $this->options[$key] = (bool)$options[$key];
                }
            }
        }
    }

    protected function isAllowed(string $category, string $action): bool
    {
        $user = $this->kirby()->user();

        if (empty($user) === true || $user->role()->id() === 'nobody') {
            return false;
        }

        if ($this->options[$action] === false) {
            return false;
        }

        if (is_bool($this->options[$action]) === true) {
            return $this->options[$action];
        }

        return $user->role()->permissions()->for($category, $action);
    }

    protected function kirby()
    {
        return $this->model()->kirby();
    }

    protected function model()
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
