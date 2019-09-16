<?php

namespace Kirby\Cms;

/**
 * UserPermissions
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier GmbH
 * @license   https://getkirby.com/license
 */
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
        // only one role, makes no sense to change it
        if ($this->user->kirby()->roles()->count() < 2) {
            return false;
        }

        // users who are not admins cannot change their own role
        if ($this->user->is($this->model) === true && $this->user->isAdmin() === false) {
            return false;
        }

        return $this->model->isLastAdmin() !== true;
    }

    protected function canCreate(): bool
    {
        // the admin can always create new users
        if ($this->user->isAdmin() === true) {
            return true;
        }

        // users who are not admins cannot create admins
        if ($this->model->isAdmin() === true) {
            return false;
        }

        return true;
    }

    protected function canDelete(): bool
    {
        return $this->model->isLastAdmin() !== true;
    }
}
