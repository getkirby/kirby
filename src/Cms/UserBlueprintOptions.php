<?php

namespace Kirby\Cms;

class UserBlueprintOptions extends BlueprintOptions
{

    protected $options = [
        'create'         => null,
        'changeEmail'    => null,
        'changeLanguage' => null,
        'changeName'     => null,
        'changePassword' => null,
        'changeRole'     => null,
        'delete'         => null,
        'update'         => null,
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
        // TODO: This should not be in the permissions request,
        // but is already tested for separately in UserRules.
        // Otherwise incorrect Exceptions are thrown.
        // if ($this->model()->isLastAdmin() === true) {
        //     return false;
        // }

        return $this->isAllowed('user', 'changeRole');
    }

    public function create(): bool
    {
        return $this->isAllowed('user', 'create');
    }

    public function delete(): bool
    {
        // TODO: This should not be in the permissions request,
        // but is already tested for separately in UserRules.
        // Otherwise incorrect Exceptions are thrown.
        // if ($this->model()->isLastAdmin() === true) {
        //     return false;
        // }

        return $this->isAllowed('user', 'delete');
    }

    protected function user()
    {
        return $this->model;
    }

    public function update(): bool
    {
        return $this->isAllowed('user', 'update');
    }

}
