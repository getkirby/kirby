<?php

use Kirby\Cms\SiteBlueprint;

return [
    'auth'    => true,
    'pattern' => 'site/blueprint',
    'action'  => function () {
        return $this->site()->blueprint()->toArray();
    }
];
