<?php

namespace Kirby\Data\Handler;

use PHPUnit\Framework\TestCase;

class YamlTest extends TestCase
{

    public function testEncodeDecode()
    {
        // the test is pretty simple.
        // the tests of the symfony
        // company can be trusted IMHO

        $array = [
            'name'     => 'Homer',
            'children' => ['Lisa', 'Bart', 'Maggie']
        ];

        $data = Yaml::encode($array);
        $this->assertEquals(
            "name: Homer\nchildren:\n  - Lisa\n  - Bart\n  - Maggie\n",
            $data
        );

        $result = Yaml::decode($data);
        $this->assertEquals($array, $result);
    }
}
