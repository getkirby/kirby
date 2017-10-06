<?php

namespace Kirby\Users\User\Auth;

use Kirby\Users\User\Auth;

class Password extends Auth
{

    protected $user;
    protected $isLoggedIn;

    public function login(array $credentials = []): bool
    {
        if (password_verify($credentials['password'] ?? null, $this->user->password()->value())) {
            $this->isLoggedIn = true;
            return true;
        } else {
            $this->isLoggedIn = false;
            return false;
        }
    }

    public function isLoggedIn(): bool
    {
        return $this->isLoggedIn;
    }

    public function logout(): bool
    {
        $this->isLoggedIn = false;
        return true;
    }

}

