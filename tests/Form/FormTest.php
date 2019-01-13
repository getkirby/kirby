<?php

namespace Kirby\Form;

use Kirby\Cms\App;
use Kirby\Data\Yaml;
use PHPUnit\Framework\TestCase;

class FormTest extends TestCase
{
    public function testDataWithoutFields()
    {
        $form = new Form([
            'fields' => [],
            'values' => $values = [
                'a' => 'A',
                'b' => 'B'
            ]
        ]);

        $this->assertEquals($values, $form->data());
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

    public function testDataFromNestedFields()
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

        $expected = [
            'structure' => [
                [
                    'tags' => 'a, b'
                ]
            ]
        ];

        $this->assertEquals($expected, $form->data());
    }

    public function testInvalidFieldType()
    {
        new App([
            'roots' => [
                'index' => '/dev/null'
            ]
        ]);

        $form = new Form([
            'fields' => [
                'test' => [
                    'type' => 'does-not-exist'
                ]
            ]
        ]);

        $field = $form->fields()->first();

        $this->assertEquals('info', $field->type());
        $this->assertEquals('negative', $field->theme());
        $this->assertEquals('Error in "test" field', $field->label());
        $this->assertEquals('<p>The field type "does-not-exist" does not exist</p>', $field->text());
    }

    public function testFieldOrder()
    {
        new App([
            'roots' => [
                'index' => '/dev/null'
            ]
        ]);

        $form = new Form([
            'fields' => [
                'a' => [
                    'type' => 'text'
                ],
                'b' => [
                    'type' => 'text'
                ]
            ],
            'values' => [
                'c' => 'C',
                'b' => 'B',
                'a' => 'A',
            ],
            'input' => [
                'b' => 'B modified'
            ]
        ]);

        $this->assertTrue(['a' => 'A', 'b' => 'B modified', 'c' => 'C'] === $form->values());
        $this->assertTrue(['a' => 'A', 'b' => 'B modified', 'c' => 'C'] === $form->data());
    }

    public function testStrictMode()
    {
        new App([
            'roots' => [
                'index' => '/dev/null'
            ]
        ]);

        $form = new Form([
            'fields' => [
                'a' => [
                    'type' => 'text'
                ],
                'b' => [
                    'type' => 'text'
                ]
            ],
            'values' => [
                'b' => 'B',
                'a' => 'A'
            ],
            'input' => [
                'c' => 'C'
            ],
            'strict' => true
        ]);

        $this->assertTrue(['a' => 'A', 'b' => 'B'] === $form->values());
        $this->assertTrue(['a' => 'A', 'b' => 'B'] === $form->data());
    }
}
