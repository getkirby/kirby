<?php

return [
    'pattern' => 'panel/autocomplete/users',
    'action'  => function () {
        return array_values(array_map(function ($user) {
            return (string)$user->email();
        }, $this->users()->toArray()));
    }
];
