<?php

return [
    'auth'    => true,
    'pattern' => 'site/files/search',
    'method'  => 'POST',
    'action'  => function () {
        return $this->output('files', $this->site(), $this->input());
    }
];
