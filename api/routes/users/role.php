<?php

return [
    'pattern' => 'users/(.*?)/role',
    'method'  => 'POST',
    'action'  => function ($id) {
        $user = $this->users()->find($id)->changeRole($this->input('role'));
        return $this->output('user', $user);
    }
];
