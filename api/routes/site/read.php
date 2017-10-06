<?php

return [
    'pattern' => 'site',
    'action'  => function () {
        return $this->output('site', $this->site());
    }
];
