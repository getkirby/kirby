<?php

return [
    'auth'    => true,
    'pattern' => 'users/search',
    'method'  => 'POST',
    'action'  => function () {
        return $this->output('collection', $this->users()->paginate(20), 'user', $this->input());
    }
];
