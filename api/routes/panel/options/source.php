<?php

use Kirby\Panel\Options\Source;

return [
    'auth'    => true,
    'pattern' => 'panel/options/source',
    'action'  => function () {

        $result = new Source([
            'site'  => $this->site(),
            'users'  => $this->users(),
            'model'  => $this->input('model'),
            'path' => $this->input('path'),
            'query' => $this->input('query'),
            'value' => $this->input('value'),
            'text' => $this->input('text')
        ]);

        return $result->toArray();

    }
];
