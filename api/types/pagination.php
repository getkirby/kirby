<?php

return function ($pagination, $arguments) {

    return [
        'total' => $pagination->total(),
        'limit' => $pagination->limit(),
        'page'  => $pagination->page()
    ];

};
