<?php

return [
    'pattern' => 'pages/(:all)',
    'method'  => 'POST',
    'action'  => function ($path) {

        $page = $this->site()
                     ->find($path)
                     ->update($this->request()->data());

        return $this->output('page', $page);

    }
];
