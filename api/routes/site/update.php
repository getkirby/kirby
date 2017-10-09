<?php

return [
    'pattern' => 'site',
    'method'  => 'POST',
    'action'  => function () {
        $site = $this->site();
        $site->update($this->request()->data());
        return $this->output('site', $site);
    }
];
