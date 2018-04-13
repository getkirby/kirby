<?php

namespace Kirby\Cms;

use Kirby\Util\Str;
use Kirby\Toolkit\V;

use Kirby\Exception\DuplicateException;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\LogicException;
use Kirby\Exception\PermissionException;

class UserRules
{

    public static function changeEmail(User $user, string $email): bool
    {
        if ($user->permissions()->changeEmail() !== true) {
            throw new PermissionException([
                'key'  => 'user.changeEmail.permission',
                'data' => ['name' => $user->name()]
            ]);
        }

        return static::validEmail($user, $email);
    }

    public static function changeLanguage(User $user, string $language): bool
    {
        if ($user->permissions()->changeLanguage() !== true) {
            throw new PermissionException([
                'key'  => 'user.changeLanguage.permission',
                'data' => ['name' => $user->name()]
            ]);
        }

        return static::validLanguage($user, $language);
    }

    public static function changeName(User $user, string $name): bool
    {
        if ($user->permissions()->changeName() !== true) {
            throw new PermissionException([
                'key'  => 'user.changeName.permission',
                'data' => ['name' => $user->name()]
            ]);
        }

        return true;
    }

    public static function changePassword(User $user, string $password): bool
    {
        if ($user->permissions()->changePassword() !== true) {
            throw new PermissionException([
                'key'  => 'user.changePassword.permission',
                'data' => ['name' => $user->name()]
            ]);
        }

        return static::validPassword($user, $password);
    }

    public static function changeRole(User $user, string $role): bool
    {
        if ($user->permissions()->changeRole() !== true) {
            throw new PermissionException([
                'key'  => 'user.changeRole.permission',
                'data' => ['name' => $user->name()]
            ]);
        }

        static::validRole($user, $role);

        if ($role !== 'admin' && $user->isLastAdmin() === true) {
            throw new LogicException([
                'key'  => 'user.changeRole.lastAdmin',
                'data' => ['name' => $user->name()]
            ]);
        }

        return true;
    }

    public static function create(User $user): bool
    {
        if ($user->kirby()->users()->count() > 0) {
            if ($user->permissions()->create() !== true) {
                throw new PermissionException([
                    'key' => 'user.create.permission'
                ]);
            }
        }

        static::validEmail($user, $user->email());
        static::validLanguage($user, $user->language());

        if ($user->password() !== null) {
            static::validPassword($user, $user->password());
        }

        return true;
    }

    public static function delete(User $user): bool
    {
        if ($user->permissions()->delete() !== true) {
            throw new PermissionException([
                'key'  => 'user.delete.permission',
                'data' => ['name' => $user->name()]
            ]);
        }

        if ($user->isLastAdmin() === true) {
            throw new LogicException(['key' => 'user.delete.lastAdmin']);
        }

        if ($user->isLastUser() === true) {
            throw new LogicException([
                'key' => 'user.delete.lastUser'
            ]);
        }

        return true;
    }

    public static function update(User $user, array $values = [], array $strings = []): bool
    {
        if ($user->permissions()->update() !== true) {
            throw new PermissionException([
                'key'  => 'user.update.permission',
                'data' => ['name' => $user->name()]
            ]);
        }

        if (isset($values['email']) === true) {
            throw new \Exception('Use the User::changeEmail() method to change the user email');
        }

        if (isset($values['password']) === true) {
            throw new \Exception('Use the User::changePassword() method to change the user password');
        }

        if (isset($values['role']) === true) {
            throw new \Exception('Use the User::changeRole() method to change the user role');
        }

        return true;
    }

    public static function validEmail(User $user, string $email): bool
    {
        if (V::email($email ?? null) === false) {
            throw new InvalidArgumentException([
                'key' => 'user.email.invalid',
            ]);
        }

        // TODO: Remove sha1() as soon as finding by email works again
        if ($duplicate = $user->kirby()->users()->not($user)->find(sha1($email))) {
            throw new DuplicateException([
                'key'  => 'user.duplicate',
                'data' => ['email' => $email]
            ]);
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
        if (is_a($user->kirby()->roles()->find($role), Role::class) === true) {
            return true;
        }

        throw new InvalidArgumentException([
            'key' => 'user.role.invalid',
        ]);
     }

}
