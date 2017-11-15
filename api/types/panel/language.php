<?php

return function ($language) {

    $result = [
        'locale'    => $language['locale'],
        'name'      => $language['title'],
        'direction' => $language['direction'],
    ];

    if (isset($language['strings'])) {
        $result['strings'] = $language['strings'];
    }

    return $result;

};
