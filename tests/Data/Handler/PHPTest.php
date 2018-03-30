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

        $data = Php::encode($array);
        $this->assertEquals(
            'a:2:{s:4:"name";s:5:"Homer";s:8:"children";a:3:{i:0;s:4:"Lisa";i:1;s:4:"Bart";i:2;s:6:"Maggie";}}',
            $data
        );

        $result = Php::decode($data);
        $this->assertEquals($array, $result);
    }

    /**
     * @expectedException        \Exception
     * @expectedExceptionMessage Serialized string is invalid
     */
    public function testDecodeCorrupted()
    {
        Php::decode('some gibberish');
    }
}
