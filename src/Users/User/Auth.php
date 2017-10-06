<?php

namespace Kirby\Users\User;

use Kirby\Users\User;

abstract class Auth
{

    protected $user;

    public function user(User $user)
    {
        $this->user = $user;
    }

    abstract public function login(array $credentials = []): bool;
    abstract public function isLoggedIn(): bool;
    abstract public function logout(): bool;

}
