<?php

namespace Kirby\Data\Handler;

use PHPUnit\Framework\TestCase;

class JsonTest extends TestCase
{

    public function testEncodeDecode()
    {
        $array = [
            'name'     => 'Homer',
            'children' => ['Lisa', 'Bart', 'Maggie']
        ];

        $data   = Json::encode($array);
        $result = Json::decode($data);

        $this->assertEquals($array, $result);
    }
}
