<?php

return array_replace_recursive(require 'TextField.php', [
    'props' => [
        'autocomplete' => 'tel',
        'icon'         => 'phone'
    ]
]);
