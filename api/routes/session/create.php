<?php

use Kirby\Data\Data;
use Kirby\FileSystem\Folder;
use Kirby\Toolkit\V;

return [
    'pattern' => 'session',
    'method'  => 'POST',
    'action'  => function () {

        $email    = $this->input('email');
        $password = $this->input('password');

        if (V::email($email) === false) {
            throw new Exception('Invalid email address');
        }

        $user = $this->app()->users()->find($email);

        if ($user === null) {
            throw new Exception('The user cannot be found');
        }

        if (empty($password)) {
            throw new Exception('Missing password');
        }

        if (password_verify($password, $user->password()->value()) === false) {
            throw new Exception('Invalid password');
        }

        $user->update([
            'token' => session_create_id()
        ]);

        return $this->output('user', $user);

    }
];
