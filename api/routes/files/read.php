<?php

return [
    'pattern' => 'files/(:all?)',
    'action'  => function ($path = null) {
        return $this->output('files', $path === null ? $this->site() : $this->site()->find($path));
    }
];
