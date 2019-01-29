<?php

namespace Kirby\Text;

use Exception;
use PHPUnit\Framework\TestCase;

class KirbyTagsTest extends TestCase
{
    public function testParse()
    {
        KirbyTag::$types = [
            'test' => [
                'html' => function () {
                    return 'test';
                }
            ]
        ];

        $this->assertEquals('test', KirbyTags::parse('(test: foo)'));
    }

    public function testParseWithException()
    {
        KirbyTag::$types = [
            'test' => [
                'html' => function () {
                    throw new Exception('Just for fun');
                }
            ]
        ];

        $this->assertEquals('(test: foo)', KirbyTags::parse('(test: foo)'));
    }
}
