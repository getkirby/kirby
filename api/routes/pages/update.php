<?php

use Kirby\Cms\Input;

return [
    'auth'    => true,
    'pattern' => 'pages/(:all)',
    'method'  => 'POST',
    'action'  => function ($path) {

        $page  = $this->site()->find($path);
        $input = $this->input();
        $data  = (new Input($page, $input))->toArray();

        return $this->output('page', $page->update($data));

    }
];
