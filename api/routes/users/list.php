<?php

return [
    'pattern' => 'users',
    'action'  => function () {
        return $this->output('collection', $this->users()->paginate(20), 'user');
    }
];
