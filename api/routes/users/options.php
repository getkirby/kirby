<?php

return [
    'pattern' => 'users/(.*?)/options',
    'action'  => function ($id) {

        $user    = $this->users()->find($id);
        $not     = explode(',', $this->input('not'));
        $options = [];

        // edit
        if (in_array('edit', $not) === false) {
            $options[] = [
                'icon'  => 'edit',
                'text'  => 'Edit',
                'link'  => '#',
                'click' => 'edit'
            ];
        }

        // change role
        if (in_array('role', $not) === false) {
            $options[] = [
                'icon'  => 'bolt',
                'text'  => 'Change role',
                'link'  => '#',
                'click' => 'role'
            ];
        }

        // change password
        if (in_array('password', $not) === false) {
            $options[] = [
                'icon'  => 'key',
                'text'  => 'Change password',
                'link'  => '#',
                'click' => 'password'
            ];
        }

        // change language
        if (in_array('language', $not) === false) {
            $options[] = [
                'icon'  => 'globe',
                'text'  => 'Change language',
                'link'  => '#',
                'click' => 'language'
            ];
        }

        // delete
        if (in_array('delete', $not) === false) {
            $options[] = [
                'icon'  => 'trash',
                'text'  => 'Delete this user',
                'link'  => '#',
                'click' => 'remove'
            ];
        }

        return $options;

    }
];
