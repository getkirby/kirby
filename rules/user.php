<?php

use Kirby\Cms\User;
use Kirby\Util\Str;
use Kirby\Toolkit\V;

return [
    'user.change.password' => function (User $user, string $password) {
        $this->rules()->check('user.valid.password', $password);
    },
    'user.change.role' => function (User $user, string $role) {
        $this->rules()->check('user.valid.role', $role);
        if ($role !== 'admin') {
            try {
                $this->rules()->check('user.is.last.admin', $user);
            } catch (Exception $e) {
                throw new Exception('The role for the last admin cannot be changed');
            }
        }
    },
    'user.create' => function (array $content = []) {
        $this->rules()->check('user.valid.email', $content['email'] ?? '');
        $this->rules()->check('user.valid.password', $content['password'] ?? '');
        $this->rules()->check('user.valid.role', $content['role'] ?? '');
        $this->rules()->check('user.exists', $content['email']);
    },
    'user.delete' => function (User $user) {
        $this->rules()->check('user.is.last.user', $user);
        $this->rules()->check('user.is.last.admin', $user);
    },
    'user.exists' => function (string $email, User $not = null) {
        if ($duplicate = $this->users()->not($not)->find($email)) {
            throw new Exception('A user with this email address already exists');
        }
    },
    'user.is.last.admin' => function (User $user) {
        if ($user->role() === 'admin' && $this->users()->filterBy('role', '==', 'admin')->count() === 1) {
            throw new Exception('The last admin cannot be removed');
        }
    },
    'user.is.last.user' => function (User $user) {
        if ($this->users()->count() === 1) {
            throw new Exception('The last user cannot be deleted');
        }
    },
    'user.update' => function (User $user, array $content = []) {

        if (isset($content['email'])) {

            $this->rules()->check('user.valid.email', $content['email']);

            if ($content['email'] !== $user->email()->value()) {
                $this->rules()->check('user.exists', $content['email'], $user);
            }

        }

        if (isset($content['password'])) {
            $this->rules()->check('user.valid.password', $content['password']);
        }

        if (isset($content['role'])) {
            $this->rules()->check('user.change.role', $user, $content['role']);
        }

    },
    'user.valid.email' => function (string $email) {
        if (V::email($email ?? null) === false) {
            throw new Exception('Please enter a valid email address');
        }
    },
    'user.valid.password' => function (string $password) {
        if (Str::length($password ?? null) < 8) {
            throw new Exception('The password must be at least 8 characters long');
        }
    },
    'user.valid.role' => function (string $role) {

        if (V::in($role, ['admin', 'editor', 'visitor']) === false) {
            throw new Exception(sprintf('Invalid user role: "%s"', $role));
        }

    },
];
