<?php

namespace Kirby\Cms;

use PHPUnit\Framework\TestCase;

class NestCollectionTest extends TestCase
{
    public function testToArray()
    {
        $collection = new NestCollection([
            new NestObject([
                'name' => 'Peter'
            ]),
            new NestObject([
                'name' => 'Paul'
            ]),
            new NestObject([
                'name' => 'Mary'
            ])
        ]);

        $expected = [
            [
                'name' => 'Peter'
            ],
            [
                'name' => 'Paul'
            ],
            [
                'name' => 'Mary'
            ]
        ];

        $this->assertEquals($expected, $collection->toArray());
    }
}
