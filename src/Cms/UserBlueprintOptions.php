<?php

namespace Kirby\Cms;

use Exception;

class UserBlueprintOptions extends BlueprintOptions
{

    protected $options = [
        'changeEmail'    => true,
        'changeLanguage' => true,
        'changePassword' => true,
        'changeRole'     => true,
        'delete'         => true,
        'read'           => true,
        'update'         => true,
    ];

    public function __construct(User $model, array $options = null)
    {
        parent::__construct($model, $options);
    }

    public function changeEmail(): bool
    {
        return $this->options['changeEmail'];
    }

    public function changeLanguage(): bool
    {
        return $this->options['changeLanguage'];
    }

    public function changePassword(): bool
    {
        return $this->options['changePassword'];
    }

    public function changeRole(): bool
    {
        if ($this->model->isLastAdmin() === true) {
            return false;
        }

        return $this->options['changeRole'];
    }

    public function delete(): bool
    {
        if ($this->model->isLastAdmin() === true) {
            return false;
        }

        return $this->options['delete'];
    }

    public function read(): bool
    {
        return $this->options['read'];
    }

    protected function user()
    {
        return $this->model;
    }

    public function update(): bool
    {
        return $this->options['update'];
    }

}
