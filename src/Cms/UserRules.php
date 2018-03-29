<?php

namespace Kirby\Cms;

use Exception;
use Kirby\Util\Str;
use Kirby\Toolkit\V;

class UserRules
{

    public static function changeEmail(User $user, string $email): bool
    {
        if ($user->permissions()->changeEmail() !== true) {
            throw new Exception('The email for this user cannot be changed');
        }

        return static::validEmail($user, $email);
    }

    public static function changeLanguage(User $user, string $language): bool
    {
        if ($user->permissions()->changeLanguage() !== true) {
            throw new Exception('The language for this user cannot be changed');
        }

        return static::validLanguage($user, $language);
    }

    public static function changeName(User $user, string $name): bool
    {
        if ($user->permissions()->changeName() !== true) {
            throw new Exception('The name for this user cannot be changed');
        }

        return true;
    }

    public static function changePassword(User $user, string $password): bool
    {
        if ($user->permissions()->changePassword() !== true) {
            throw new Exception('The password for this user cannot be changed');
        }

        return static::validPassword($user, $password);
    }

    public static function changeRole(User $user, string $role): bool
    {
        if ($user->permissions()->changeRole() !== true) {
            throw new Exception('The role for this user cannot be changed');
        }

        static::validRole($user, $role);

        if ($role !== 'admin' && $user->isLastAdmin() === true) {
            throw new Exception('The role for the last admin cannot be changed');
        }

        return true;
    }

    public static function create(User $user): bool
    {
        if ($user->kirby()->users()->count() > 0) {
            if ($user->permissions()->create() !== true) {
                throw new Exception('This user cannot be created');
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
            throw new Exception('The user cannot be deleted');
        }

        if ($user->isLastAdmin() === true) {
            throw new Exception('The last admin cannot be deleted');
        }

        if ($user->isLastUser() === true) {
            throw new Exception('The last user cannot be deleted');
        }

        return true;
    }

    public static function update(User $user, array $values = [], array $strings = []): bool
    {
        if ($user->permissions()->update() !== true) {
            throw new Exception('The user cannot be updated');
        }

        if (isset($values['email']) === true) {
            throw new Exception('Use the User::changeEmail() method to change the user email');
        }

        if (isset($values['password']) === true) {
            throw new Exception('Use the User::changePassword() method to change the user password');
        }

        if (isset($values['role']) === true) {
            throw new Exception('Use the User::changeRole() method to change the user role');
        }

        return true;
    }

    public static function validEmail(User $user, string $email): bool
    {
        if (V::email($email ?? null) === false) {
            throw new Exception('Please enter a valid email address');
        }

        // TODO: Remove sha1() as soon as finding by email works again
        if ($duplicate = $user->kirby()->users()->not($user)->find(sha1($email))) {
            throw new Exception('A user with this email address already exists');
        }

        return true;
    }

    public static function validLanguage(User $user, string $language): bool
    {
        if (in_array($language, $user->kirby()->locales()->keys(), true) === false) {
            throw new Exception('Invalid user language');
        }

        return true;
    }

    public static function validPassword(User $user, string $password): bool
    {
        if (Str::length($password ?? null) < 8) {
            throw new Exception('The password must be at least 8 characters long');
        }

        return true;
    }

    public static function validRole(User $user, string $role): bool
    {
        if (is_a($user->kirby()->roles()->find($role), Role::class) === true) {
            return true;
        }

        throw new Exception(sprintf('Invalid user role: "%s"', $role));
    }

}
