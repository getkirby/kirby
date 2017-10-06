<?php

return function ($language, $arguments) {

    return [
        'locale'    => $language['locale'],
        'name'      => $language['title'],
        'direction' => $language['direction']
    ];

};
