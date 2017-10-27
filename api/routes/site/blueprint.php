<?php

use Kirby\Cms\SiteBlueprint;

return [
    'pattern' => 'site/blueprint',
    'action'  => function () {
        return (new SiteBlueprint())->toArray();
    }
];
