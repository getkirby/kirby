<?php

return [
    'auth'    => true,
    'pattern' => 'users/(.*?)',
    'action'  => function ($email) {
        return $this->output('user', $this->users()->findBy('email', $email));
    }
];
