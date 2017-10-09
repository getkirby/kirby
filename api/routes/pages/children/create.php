<?php

return [
    'pattern' => 'pages/(:all)/children',
    'method'  => 'POST',
    'action'  => function ($path) {

        $child = $this->site()->find($path)->child([
            'template' => $this->input('template'),
            'slug'     => $this->input('slug'),
            'content'  => $this->input('content')
        ]);

        $child->save();

        return $this->output('page', $child);
    }
];
