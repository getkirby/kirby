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
    /**
     * @var string
     */
    protected $category = 'users';

    /**
     * @var \Kirby\Cms\User
     */
    protected $model;

    /**
     * UserPermissions constructor
     *
     * @param \Kirby\Cms\User $model
     */
    public function __construct(User $model)
    {
        parent::__construct($model);

        // change the scope of the permissions, when the current user is this user
        $this->category = $this->user && $this->user->is($model) ? 'user' : 'users';
    }

    /**
     * @return bool
     */
    protected function canChangeRole(): bool
    {
        $roles = $this->model->kirby()->roles();

        // authenticated admins can always change the role of
        // a user unless the user is the last admin
        if ($this->user->isAdmin() === true) {
            if ($this->model->isLastAdmin() === true) {
                return false;
            }

            return $roles->count() > 1;
        }

        // if the user is an admin and the authenticated
        // user isn't, the authenticated user cannot
        // change the role. No matter how the permissions
        // are set.
        if ($this->model->isAdmin() === true) {
            return false;
        }

        // non-admins cannot promote users to admins
        $roles = $roles->filter('id', '!=', 'admin');

        return $roles->count() > 1;
    }

    /**
     * @return bool
     */
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

    /**
     * @return bool
     */
    protected function canDelete(): bool
    {
        return $this->model->isLastAdmin() !== true;
    }
}
