<?php

use Kirby\Users\User;
use Kirby\Users\User\Store;
use Kirby\Users\User\Avatar;

return [
    'pattern' => 'users',
    'method'  => 'POST',
    'action'  => function () {

        $data = $this->input();
        $user = new User([
            'id'     => $id = $data['email'],
            'data'   => $data,
            'store'  => new Store([
                'root' => $this->app()->root('accounts')  . '/' . $id
            ]),
            'avatar' => new Avatar([
                'url'  => $this->app()->url('accounts')  . '/' . $id . '/profile.jpg',
                'root' => $this->app()->root('accounts') . '/' . $id . '/profile.jpg'
            ])
        ]);

        $user->save();

        return $this->output('user', $user);
    }
];
