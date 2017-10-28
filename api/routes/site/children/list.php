<?php

return [
    'auth'    => true,
    'pattern' => 'site/children',
    'action'  => function () {
        return $this->output('children', $this->site(), $this->query());
    }
];
