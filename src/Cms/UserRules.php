<?php

namespace Kirby\Cms;

use Kirby\Exception\DuplicateException;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\LogicException;
use Kirby\Exception\PermissionException;
use Kirby\Toolkit\Str;
use Kirby\Toolkit\V;

/**
 * Validators for all user actions
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier GmbH
 * @license   https://getkirby.com/license
 */
class UserRules
{
    public static function changeEmail(User $user, string $email): bool
    {
        if ($user->permissions()->changeEmail() !== true) {
            throw new PermissionException([
                'key'  => 'user.changeEmail.permission',
                'data' => ['name' => $user->username()]
            ]);
        }

        return static::validEmail($user, $email);
    }

    public static function changeLanguage(User $user, string $language): bool
    {
        if ($user->permissions()->changeLanguage() !== true) {
            throw new PermissionException([
                'key'  => 'user.changeLanguage.permission',
                'data' => ['name' => $user->username()]
            ]);
        }

        return static::validLanguage($user, $language);
    }

    public static function changeName(User $user, string $name): bool
    {
        if ($user->permissions()->changeName() !== true) {
            throw new PermissionException([
                'key'  => 'user.changeName.permission',
                'data' => ['name' => $user->username()]
            ]);
        }

        return true;
    }

    public static function changePassword(User $user, string $password): bool
    {
        if ($user->permissions()->changePassword() !== true) {
            throw new PermissionException([
                'key'  => 'user.changePassword.permission',
                'data' => ['name' => $user->username()]
            ]);
        }

        return static::validPassword($user, $password);
    }

    public static function changeRole(User $user, string $role): bool
    {
        // protect admin from role changes by non-admin
        if (
            $user->kirby()->user()->isAdmin() === false &&
            $user->isAdmin() === true
        ) {
            throw new PermissionException([
                'key'  => 'user.changeRole.permission',
                'data' => ['name' => $user->username()]
            ]);
        }

        // prevent non-admins making a user to admin
        if (
            $user->kirby()->user()->isAdmin() === false &&
            $role === 'admin'
        ) {
            throw new PermissionException([
                'key'  => 'user.changeRole.toAdmin'
            ]);
        }

        static::validRole($user, $role);

        if ($role !== 'admin' && $user->isLastAdmin() === true) {
            throw new LogicException([
                'key'  => 'user.changeRole.lastAdmin',
                'data' => ['name' => $user->username()]
            ]);
        }

        if ($user->permissions()->changeRole() !== true) {
            throw new PermissionException([
                'key'  => 'user.changeRole.permission',
                'data' => ['name' => $user->username()]
            ]);
        }

        return true;
    }

    public static function create(User $user, array $props = []): bool
    {
        static::validId($user, $user->id());
        static::validEmail($user, $user->email(), true);
        static::validLanguage($user, $user->language());
        
        if (empty($props['password']) === false) {
            static::validPassword($user, $props['password']);
        }

        // get the current user if it exists
        $currentUser = $user->kirby()->user();
        
        // admins are allowed everything
        if ($currentUser && $currentUser->isAdmin() === true) {
            return true;
        }

        // only admins are allowed to add admins
        $role = $props['role'] ?? null;

        if ($role === 'admin' && $currentUser && $currentUser->isAdmin() === false) {
            throw new PermissionException([
                'key' => 'user.create.permission'
            ]);
        }
        
        // check user permissions (if not on install)
        if ($user->kirby()->users()->count() > 0) {
            if ($user->permissions()->create() !== true) {
                throw new PermissionException([
                    'key' => 'user.create.permission'
                ]);
            }
        }

        return true;
    }

    public static function delete(User $user): bool
    {
        if ($user->isLastAdmin() === true) {
            throw new LogicException(['key' => 'user.delete.lastAdmin']);
        }

        if ($user->isLastUser() === true) {
            throw new LogicException([
                'key' => 'user.delete.lastUser'
            ]);
        }

        if ($user->permissions()->delete() !== true) {
            throw new PermissionException([
                'key'  => 'user.delete.permission',
                'data' => ['name' => $user->username()]
            ]);
        }

        return true;
    }

    public static function update(User $user, array $values = [], array $strings = []): bool
    {
        if ($user->permissions()->update() !== true) {
            throw new PermissionException([
                'key'  => 'user.update.permission',
                'data' => ['name' => $user->username()]
            ]);
        }

        return true;
    }

    public static function validEmail(User $user, string $email, bool $strict = false): bool
    {
        if (V::email($email ?? null) === false) {
            throw new InvalidArgumentException([
                'key' => 'user.email.invalid',
            ]);
        }

        if ($strict === true) {
            $duplicate = $user->kirby()->users()->find($email);
        } else {
            $duplicate = $user->kirby()->users()->not($user)->find($email);
        }

        if ($duplicate) {
            throw new DuplicateException([
                'key'  => 'user.duplicate',
                'data' => ['email' => $email]
            ]);
        }

        return true;
    }

    public static function validId(User $user, string $id): bool
    {
        if ($user->kirby()->users()->find($id)) {
            throw new DuplicateException('A user with this id exists');
        }

        return true;
    }

    public static function validLanguage(User $user, string $language): bool
    {
        if (in_array($language, $user->kirby()->translations()->keys(), true) === false) {
            throw new InvalidArgumentException([
                'key' => 'user.language.invalid',
            ]);
        }

        return true;
    }

    public static function validPassword(User $user, string $password): bool
    {
        if (Str::length($password ?? null) < 8) {
            throw new InvalidArgumentException([
                'key' => 'user.password.invalid',
            ]);
        }

        return true;
    }

    public static function validRole(User $user, string $role): bool
    {
        if (is_a($user->kirby()->roles()->find($role), 'Kirby\Cms\Role') === true) {
            return true;
        }

        throw new InvalidArgumentException([
            'key' => 'user.role.invalid',
        ]);
    }
}
