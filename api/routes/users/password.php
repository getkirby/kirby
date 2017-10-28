<?php

return [
    'auth'    => true,
    'pattern' => 'users/(.*?)/password',
    'method'  => 'POST',
    'action'  => function ($id) {
        $user = $this->users()->find($id)->changePassword($this->input('password'));
        return $this->output('user', $user);
    }
];
