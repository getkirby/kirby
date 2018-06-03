<?php

namespace Kirby\Toolkit;

use PHPUnit\Framework\TestCase;

class HtmlTest extends TestCase
{

    public function testAttr()
    {

        $tests = [
            [
                'input'    => [],
                'expected' => ''
            ],
            [
                'input'    => ['a' => 'a', 'b' => 'b'],
                'expected' => 'a="a" b="b"'
            ],
            [
                'input'    => ['a' => 'a', 'b' => true],
                'expected' => 'a="a" b'
            ],
            [
                'input'    => ['a' => 'a', 'b' => ''],
                'expected' => 'a="a"'
            ],
            [
                'input'    => ['a' => 'a', 'b' => false],
                'expected' => 'a="a"'
            ],
        ];

        foreach($tests as $test) {
            $result = Html::attr($test['input']);
            $this->assertEquals($test['expected'], $result);
        }

    }

}
