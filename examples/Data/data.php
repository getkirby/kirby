<?php

require '../vendor/autoload.php';

use Kirby\Data\Data;

$user = [
    'name'  => 'Homer Simpson',
    'email' => 'homer@simpson.com'
];

// json
Data::write('data/data.json', $user);

var_dump(Data::read('data/data.json'));

// yaml
Data::write('data/data.yml', $user);

var_dump(Data::read('data/data.yml'));

// txt
Data::write('data/data.txt', $user);

var_dump(Data::read('data/data.txt'));

// php
Data::write('data/data.php', $user);

var_dump(Data::read('data/data.php'));
