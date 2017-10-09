<?php

return [
    'pattern' => 'users',
    'method'  => ['GET', 'POST'],
    'action'  => function () {
        return $this->output('collection', $this->users()->paginate(20), 'user', $this->query());
    }
];
