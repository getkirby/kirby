<?php

use Kirby\Panel\Options\Query;

return [
    'auth'    => true,
    'pattern' => 'panel/options/query',
    'action'  => function () {

        $result = new Query([
            'site'  => $this->site(),
            'page'  => $this->input('page'),
            'fetch' => $this->input('fetch'),
            'template' => $this->input('template'),
            'value' => $this->input('value'),
            'text' => $this->input('text'),
            'flip' => $this->input('flip') === 'true'
        ]);

        return $result->toArray();

    }
];
