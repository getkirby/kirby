<?php

namespace Kirby\Cms;

/**
 * Normalizes user options in user blueprints
 * and checks for each option, if the current
 * user is allowed to execute it.
 */
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
        return $this->isAllowed('users', 'changeEmail');
    }

    public function changeLanguage(): bool
    {
        return $this->isAllowed('users', 'changeLanguage');
    }

    public function changeName(): bool
    {
        return $this->isAllowed('users', 'changeName');
    }

    public function changePassword(): bool
    {
        return $this->isAllowed('users', 'changePassword');
    }

    public function changeRole(): bool
    {
        // TODO: This should not be in the permissions request,
        // but is already tested for separately in UserRules.
        // Otherwise incorrect Exceptions are thrown.
        // if ($this->model()->isLastAdmin() === true) {
        //     return false;
        // }

        return $this->isAllowed('users', 'changeRole');
    }

    public function create(): bool
    {
        return $this->isAllowed('users', 'create');
    }

    public function delete(): bool
    {
        // TODO: This should not be in the permissions request,
        // but is already tested for separately in UserRules.
        // Otherwise incorrect Exceptions are thrown.
        // if ($this->model()->isLastAdmin() === true) {
        //     return false;
        // }

        return $this->isAllowed('users', 'delete');
    }

    protected function user()
    {
        return $this->model;
    }

    public function update(): bool
    {
        return $this->isAllowed('users', 'update');
    }
}
