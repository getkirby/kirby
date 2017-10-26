<?php

use Kirby\Cms\User;
use Kirby\Cms\Avatar;
use Kirby\FileSystem\File;

return [
    'pattern' => 'users/(.*?)/avatar',
    'method'  => ['POST', 'OPTIONS'],
    'action'  => function ($id) {

        $user    = $this->users()->find($id);
        $request = $this->request();

        if ($request->method() === 'OPTIONS') {
            return true;
        }

        foreach ($request->files()->data() as $file) {
            Avatar::create($user, $file['tmp_name']);
        }

        return $this->output('user', $user);

    }
];
