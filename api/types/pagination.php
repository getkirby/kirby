<?php

return function ($pagination) {

    return [
        'total' => $pagination->total(),
        'limit' => $pagination->limit(),
        'page'  => $pagination->page()
    ];

};
