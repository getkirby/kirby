<?php

return [
    'auth'    => true,
    'pattern' => 'site/files',
    'action'  => function () {
        return $this->output('files', $this->site(), $this->query());
    }
];
