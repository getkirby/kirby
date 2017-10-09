<?php

return function ($language) {

    return [
        'locale'    => $language['locale'],
        'name'      => $language['title'],
        'direction' => $language['direction']
    ];

};
