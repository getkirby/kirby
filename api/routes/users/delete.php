<?php

return [
    'pattern' => 'users/(.*?)',
    'method'  => 'DELETE',
    'action'  => function ($email) {
        return $this->users()->findBy('email', $email)->delete();
    }
];
