<?php

return function ($user) {

    $output = [
        'id'       => $user->id(),
        'content'  => $user->content()->not('password', 'role')->toArray(),
        'role'     => $user->role(),
        'language' => $user->language(),
        'image'    => $this->output('avatar', $user->avatar()),
    ];

    if ($prev = $user->prev()) {
        $output['prev'] = $prev->email()->value();
    }

    if ($next = $user->next()) {
        $output['next'] = $next->email()->value();
    }

    return $output;

};
