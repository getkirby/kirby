<?php

return [
    'pattern' => 'users/(.*?)/options',
    'action'  => function ($id) {

        $user    = $this->users()->find($id);
        $options = [];

        // edit
        $options[] = [
            'icon'  => 'edit',
            'text'  => 'Edit',
            'link'  => '#',
            'click' => 'edit'
        ];

        // change role
        $options[] = [
            'icon'  => 'bolt',
            'text'  => 'Role',
            'link'  => '#',
            'click' => 'role'
        ];

        // change password
        $options[] = [
            'icon'  => 'key',
            'text'  => 'Password',
            'link'  => '#',
            'click' => 'password'
        ];

        // delete
        $options[] = [
            'icon'  => 'trash',
            'text'  => 'Delete',
            'link'  => '#',
            'click' => 'remove'
        ];

        return $options;

    }
];
