<?php

return [
    'auth'    => true,
    'pattern' => 'site',
    'method'  => 'POST',
    'action'  => function () {
        $site = $this->site()->update($this->request()->data());
        return $this->output('site', $site);
    }
];
