<?php

return [
    'pattern' => 'site/children/search',
    'method'  => 'POST',
    'action'  => function () {
        return $this->output('children', $this->site(), $this->input());
    }
];
