<?php

namespace Kirby\Cms;

use Exception;
use Kirby\Util\Str;
use Kirby\Toolkit\V;

class UserRules
{

    public static function changeEmail(User $user, string $email): bool
    {
        return static::validEmail($user, $email);
    }

    public static function changeLanguage(User $user, string $language): bool
    {
        return static::validLanguage($user, $language);
    }

    public static function changePassword(User $user, string $password): bool
    {
        return static::validPassword($user, $password);
    }

    public static function changeRole(User $user, string $role): bool
    {
        static::validRole($user, $role);

        if ($role !== 'admin' && $user->isLastAdmin() === true) {
            throw new Exception('The role for the last admin cannot be changed');
        }

        return true;
    }

    // TODO: $form not used?
    public static function create(User $user, Form $form): bool
    {
        static::validEmail($user, $user->email());
        static::validRole($user, $user->role());
        static::validLanguage($user, $user->language());

        if ($user->password() !== '') {
            static::validPassword($user, $user->password());
        }

        return true;
    }

    public static function delete(User $user): bool
    {
        if ($user->isLastAdmin() === true) {
            throw new Exception('The last admin cannot be deleted');
        }

        if ($user->isLastUser() === true) {
            throw new Exception('The last user cannot be deleted');
        }

        return true;
    }

    public static function update(User $user, array $content = [], Form $form): bool
    {
        if (isset($content['email']) === true) {
            throw new Exception('Use the User::changeEmail() method to change the user email');
        }

        if (isset($content['password']) === true) {
            throw new Exception('Use the User::changePassword() method to change the user password');
        }

        if (isset($content['role']) === true) {
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
        if (V::in($role, ['admin', 'editor', 'visitor']) === false) {
            throw new Exception(sprintf('Invalid user role: "%s"', $role));
        }

        return true;
    }

}
