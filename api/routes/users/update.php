<?php

return [
    'pattern' => 'users/(.*?)',
    'method'  => 'POST',
    'action'  => function ($email) {

        $user = $this->users()
                     ->findBy('email', $email)
                     ->update($this->request()->data());

        return $this->output('user', $user);

    }
];
