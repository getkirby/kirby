<?php

use Kirby\Panel\Options\Source;

return [
    'auth'    => true,
    'pattern' => 'panel/options/source',
    'action'  => function () {

        $result = new Source([
            'site'  => $this->site(),
            'users'  => $this->users(),
            'page' => $this->input('page'),
            'file' => $this->input('file'),
            'user'  => $this->input('user'),
            'query' => $this->input('query'),
            'value' => $this->input('value'),
            'text' => $this->input('text')
        ]);

        return $result->toArray();

    }
];
