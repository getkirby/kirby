<?php

namespace Kirby\Data\Handler;

use PHPUnit\Framework\TestCase;

class YamlTest extends TestCase
{

    public function testEncodeDecode()
    {
        $array = [
            'name'     => 'Homer',
            'children' => ['Lisa', 'Bart', 'Maggie']
        ];

        $yaml   = Yaml::encode($array);
        $result = Yaml::decode($yaml);

        // the test is pretty simple.
        // the tests of the symfony
        // company can be trusted IMHO
        $this->assertEquals($array, $result);
    }
}
