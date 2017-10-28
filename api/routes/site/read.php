<?php

return [
    'auth'    => true,
    'pattern' => 'site',
    'action'  => function () {
        return $this->output('site', $this->site());
    }
];
