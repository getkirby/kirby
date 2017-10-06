<?php

return [
    'pattern' => 'users',
    'action'  => function ($path = null) {
        return $this->output('collection', $this->users()->paginate(20), 'user');
    }
];
