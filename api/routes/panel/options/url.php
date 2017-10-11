<?php

use Kirby\Panel\Options\Url;

return [
    'pattern' => 'panel/options/url',
    'action'  => function () {

        $result = new Url([
            'url'  => $this->input('url'),
        ]);

        return $result->toArray();

    }
];
