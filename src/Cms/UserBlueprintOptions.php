<?php

namespace Kirby\Cms;

use Exception;

class UserBlueprintOptions extends BlueprintOptions
{

    protected $options = [
        'create'         => true,
        'changeEmail'    => true,
        'changeLanguage' => true,
        'changeName'     => true,
        'changePassword' => true,
        'changeRole'     => true,
        'delete'         => true,
        'update'         => true,
    ];

    public function __construct(User $model, array $options = null)
    {
        parent::__construct($model, $options);
    }

    public function changeEmail(): bool
    {
        return $this->isAllowed('user', 'changeEmail');
    }

    public function changeLanguage(): bool
    {
        return $this->isAllowed('user', 'changeLanguage');
    }

    public function changeName(): bool
    {
        return $this->isAllowed('user', 'changeName');
    }

    public function changePassword(): bool
    {
        return $this->isAllowed('user', 'changePassword');
    }

    public function changeRole(): bool
    {
        if ($this->model->isLastAdmin() === true) {
            return false;
        }

        return $this->isAllowed('user', 'changeRole');
    }

    public function create(): bool
    {
        return $this->isAllowed('user', 'create');
    }

    public function delete(): bool
    {
        if ($this->model->isLastAdmin() === true) {
            return false;
        }

        return $this->isAllowed('user', 'delete');
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
