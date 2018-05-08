<?php

Kirby::plugin('my/view', [
    'hooks' => [
        'page.delete:before' => function() {
            // throw new Exception("nope: " . $this->user()->name());
        }
    ]
]);
