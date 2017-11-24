<?php

namespace Kirby\Data\Handler;

use PHPUnit\Framework\TestCase;

class PhpTest extends TestCase
{

    public function testEncodeDecode()
    {
        $array = [
            'name'     => 'Homer',
            'children' => ['Lisa', 'Bart', 'Maggie']
        ];

        $data   = Php::encode($array);
        $result = Php::decode($data);

        $this->assertEquals($array, $result);
    }
}
