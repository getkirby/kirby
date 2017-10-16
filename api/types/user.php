<?php

return function ($user) {

    $output = [
        'id'   => $user->id(),
        'data' => $user->data()->not('password', 'role')->toArray(),
        'role' => $user->role(),
        'image' => [
            'url' => $user->avatar()->url()
        ]
    ];

    if ($prev = $user->prev()) {
        $output['prev'] = $prev->email()->value();
    }

    if ($next = $user->next()) {
        $output['next'] = $next->email()->value();
    }

    return $output;

};
