<?php

namespace Kirby\Cms;

class BlueprintConverterTest extends TestCase
{

    public function testConvertWithoutChanges()
    {
        $blueprint = [
            'tabs' => []
        ];

        $converted = BlueprintConverter::convert($blueprint);
        $this->assertEquals($converted, $blueprint);
    }

    public function testConvertColumns()
    {
        $blueprint = [
            'columns' => []
        ];

        $expected = [
            'tabs' => [
                [
                    'name'    => 'main',
                    'columns' => []
                ]
            ]
        ];

        $converted = BlueprintConverter::convert($blueprint);
        $this->assertEquals($expected, $converted);
    }

    public function testConvertFields()
    {
        $blueprint = [
            'fields' => []
        ];

        $expected = [
            'tabs' => [
                [
                    'name'    => 'main',
                    'columns' => [
                        [
                            'type'   => 'fields',
                            'fields' => []
                        ]
                    ]
                ]
            ]
        ];

        $converted = BlueprintConverter::convert($blueprint);
        $this->assertEquals($expected, $converted);
    }


}
