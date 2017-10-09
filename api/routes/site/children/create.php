<?php

return [
    'pattern' => 'site/children',
    'method'  => 'POST',
    'action'  => function () {

        $child = $this->site()->child([
            'template' => $this->input('template'),
            'slug'     => $this->input('slug'),
            'content'  => $this->input('content')
        ]);

        $child->save();

        return $this->output('page', $child);
    }
];
