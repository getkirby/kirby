<?php

return [
    'pattern' => 'session',
    'action'  => function () {
        return $this->output('user', $this->user());
    }
];
