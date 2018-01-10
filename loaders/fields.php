<?php

// setup the field loader
return function ($type, $name) {

    $name = basename($name);
    $file = dirname(__DIR__) . '/fields';

    if ($type === 'mixin') {
        $file .= '/_mixins/' . $name . '.php';
    } else {
        $file .= '/' . $name . '/' . $name . '.php';
    }

    if (file_exists($file) === false) {
        return null;
    }

    return require $file;

};
