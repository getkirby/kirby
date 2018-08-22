<?php

namespace Kirby\Cms;

class UserPermissions extends ModelPermissions
{
    protected $category = 'users';

    public function __construct(Model $model)
    {
        parent::__construct($model);

        // change the scope of the permissions, when the current user is this user
        $this->category = $this->user && $this->user->is($model) ? 'user' : 'users';
    }

    protected function canChangeRole(): bool
    {
        return $this->model->isLastAdmin() !== true;
    }

    protected function canDelete(): bool
    {
        return $this->model->isLastAdmin() !== true;
    }
}
