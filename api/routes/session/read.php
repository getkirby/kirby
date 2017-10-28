<?php

return [
    'auth'    => true,
    'pattern' => 'session',
    'action'  => function () {
        return $this->output('user', $this->user());
    }
];
