<?php

namespace Kirby\Form;

use Kirby\Cms\App;
use Kirby\Data\Yaml;
use PHPUnit\Framework\TestCase;

class FormTest extends TestCase
{

    public function testStringsWithoutFields()
    {
        $form = new Form([
            'fields' => [],
            'values' => $values = [
                'a' => 'A',
                'b' => 'B'
            ]
        ]);

        $this->assertEquals($values, $form->strings());
    }

    public function testValuesWithoutFields()
    {
        $form = new Form([
            'fields' => [],
            'values' => $values = [
                'a' => 'A',
                'b' => 'B'
            ]
        ]);

        $this->assertEquals($values, $form->values());
    }

    public function testStringsFromNestedFields()
    {

        new App([
            'roots' => [
                'index' => '/dev/null'
            ]
        ]);

        $form = new Form([
            'fields' => [
                'structure' => [
                    'type'   => 'structure',
                    'fields' => [
                        'tags' => [
                            'type' => 'tags'
                        ]
                    ]
                ]
            ],
            'values' => $values = [
                'structure' => [
                    [
                        'tags' => 'a, b'
                    ]
                ]
            ]
        ]);

        $expectedYaml = [
            'structure' => Yaml::encode([
                [
                    'tags' => 'a, b'
                ]
            ])
        ];

        $this->assertEquals($expectedYaml, $form->strings());

    }

}
