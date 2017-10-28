<?php

return [
    'auth'    => true,
    'pattern' => 'users',
    'method'  => 'GET',
    'action'  => function () {
        return $this->output('collection', $this->users()->paginate(20), 'user', $this->query());
    }
];
