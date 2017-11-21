<?php

require '../vendor/autoload.php';

use Kirby\Content\Field;

$field = new Field('email', 'support@getkirby.com');

var_dump($field->key());
var_dump($field->value());

// register a field method
$field->method('upper', function() {
    return $this->value(function($value) {
        return strtoupper($value);
    });
});

var_dump($field->upper());
