<?php

require '../vendor/autoload.php';

use Kirby\Data\Data;
use Kirby\Data\Handler;

class CustomHandler extends Handler
{

    public static function encode(array $data = []): string
    {

        $xml[] = '<user>';

        foreach ($data as $key => $value) {
            $xml[] = '<' . $key . '>' . $value . '</' . $key . '>';
        }

        $xml[] = '</user>';

        return implode(PHP_EOL, $xml);

    }

    public static function decode(string $data): array
    {
        return (array)simplexml_load_string($data);
    }

}

$user = [
    'name'  => 'Homer Simpson',
    'email' => 'homer@simpson.com'
];

Data::handler('xml', 'CustomHandler');
Data::write('data/data.xml', $user);

var_dump(Data::read('data/data.xml'));

