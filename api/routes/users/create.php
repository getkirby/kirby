<?php

use Kirby\Cms\User;

return [
    'pattern' => 'users',
    'method'  => 'POST',
    'action'  => function () {

        $user = User::create([
            'email'    => $this->input('email'),
            'password' => $this->input('password'),
            'language' => $this->input('language'),
            'role'     => $this->input('role'),
        ]);

        return $this->output('user', $user);
    }
];
