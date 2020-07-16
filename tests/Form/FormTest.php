<?php

namespace Kirby\Form;

use Kirby\Cms\App;
use Kirby\Cms\Page;
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

    public function testDataFromUnsaveableFields()
    {
        new App([
            'roots' => [
                'index' => '/dev/null'
            ]
        ]);

        $page = new Page(['slug' => 'test']);
        $form = new Form([
            'fields' => [
                'info' => [
                    'type' => 'info',
                    'model' => $page
                ]
            ],
            'values' => [
                'info' => 'Yay'
            ]
        ]);

        $this->assertNull($form->data()['info']);
    }

    public function testDataFromNestedFields()
    {
        new App([
            'roots' => [
                'index' => '/dev/null'
            ]
        ]);

        $page = new Page(['slug' => 'test']);
        $form = new Form([
            'fields' => [
                'structure' => [
                    'type'   => 'structure',
                    'model' => $page,
                    'fields' => [
                        'tags' => [
                            'type'  => 'tags',
                            'model' => $page
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

        $page = new Page(['slug' => 'test']);
        $form = new Form([
            'fields' => [
                'test' => [
                    'type'  => 'does-not-exist',
                    'model' => $page
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

        $page = new Page(['slug' => 'test']);
        $form = new Form([
            'fields' => [
                'a' => [
                    'type'  => 'text',
                    'model' => $page
                ],
                'b' => [
                    'type'  => 'text',
                    'model' => $page
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

        $page = new Page(['slug' => 'test']);
        $form = new Form([
            'fields' => [
                'a' => [
                    'type' => 'text',
                    'model' => $page
                ],
                'b' => [
                    'type' => 'text',
                    'model' => $page
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

    public function testErrors()
    {
        new App([
            'roots' => [
                'index' => '/dev/null'
            ]
        ]);

        $page = new Page(['slug' => 'test']);
        $form = new Form([
            'fields' => [
                'a' => [
                    'label' => 'Email',
                    'type' => 'email',
                    'model' => $page
                ],
                'b' => [
                    'label' => 'Url',
                    'type' => 'url',
                    'model' => $page
                ]
            ],
            'values' => [
                'a' => 'A',
                'b' => 'B',
            ]
        ]);


        $this->assertTrue($form->isInvalid());
        $this->assertFalse($form->isValid());

        $expected = [
            'a' => [
                'label' => 'Email',
                'message' => [
                    'email' => 'Please enter a valid email address'
                ]
            ],
            'b' => [
                'label' => 'Url',
                'message' => [
                    'url' => 'Please enter a valid URL'
                ]
            ]
        ];

        $this->assertEquals($expected, $form->errors());

        // check for a correct cached array
        $this->assertEquals($expected, $form->errors());
    }

    public function testToArray()
    {
        new App([
            'roots' => [
                'index' => '/dev/null'
            ]
        ]);

        $page = new Page(['slug' => 'test']);
        $form = new Form([
            'fields' => [
                'a' => [
                    'label' => 'A',
                    'type' => 'text',
                    'model' => $page
                ],
                'b' => [
                    'label' => 'B',
                    'type' => 'text',
                    'model' => $page
                ]
            ],
            'values' => [
                'a' => 'A',
                'b' => 'B',
            ]
        ]);

        $this->assertEquals([], $form->toArray()['errors']);
        $this->assertArrayHasKey('a', $form->toArray()['fields']);
        $this->assertArrayHasKey('b', $form->toArray()['fields']);
        $this->assertCount(2, $form->toArray()['fields']);
        $this->assertEquals(false, $form->toArray()['invalid']);
    }

    public function testContent()
    {
        $form = new Form([
            'fields' => [],
            'values' => $values = [
                'a' => 'A',
                'b' => 'B'
            ]
        ]);

        $this->assertEquals($values, $form->content());
    }

    public function testContentFromUnsaveableFields()
    {
        new App([
            'roots' => [
                'index' => '/dev/null'
            ]
        ]);

        $page = new Page(['slug' => 'test']);
        $form = new Form([
            'fields' => [
                'info' => [
                    'type' => 'info',
                    'model' => $page
                ]
            ],
            'values' => [
                'info' => 'Yay'
            ]
        ]);

        $this->assertCount(0, $form->content());
        $this->assertArrayNotHasKey('info', $form->content());
        $this->assertCount(1, $form->data());
        $this->assertArrayHasKey('info', $form->data());
    }
}
