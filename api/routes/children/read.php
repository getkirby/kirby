<?php

return [
    'pattern' => 'children/(:all?)',
    'action'  => function ($path = null) {
        return $this->output('children', $path === null ? $this->site() : $this->site()->find($path));
    }
];
