<?php

namespace Kirby\Cms;

class FormTest extends TestCase
{
    public function testPageForm()
    {
        $page = new Page([
            'slug' => 'test',
            'content' => [
                'title' => 'Test',
                'date'  => '2012-12-12'
            ],
            'blueprint' => [
                'title' => 'Test',
                'name' => 'test',
                'fields' => [
                    'date' => [
                        'type' => 'date'
                    ]
                ]
            ]
        ]);

        $form = Form::for($page, [
            'values' => [
                'title' => 'Updated Title',
                'date'  => null
            ]
        ]);

        $values = $form->values();

        // the title must always be transfered, even if not in the blueprint
        $this->assertEquals('Updated Title', $values['title']);

        // empty fields should be actually empty
        $this->assertNull($values['date']);
    }

    public function testPageFormWithClosures()
    {
        $page = new Page([
            'slug' => 'test',
            'content' => [
                'a' => 'A'
            ]
        ]);

        $form = Form::for($page, [
            'values' => [
                'a' => function ($value) {
                    return $value . 'A';
                },
                'b' => function ($value) {
                    return $value . 'B';
                },
            ]
        ]);

        $values = $form->values();

        $this->assertEquals('AA', $values['a']);
        $this->assertEquals('B', $values['b']);
    }

    public function testFileFormWithoutBlueprint()
    {
        new App([
            'roots' => [
                'index' => '/dev/null'
            ]
        ]);

        $file = new File([
            'filename' => 'test.jpg',
            'content'  => []
        ]);

        $form = Form::for($file, [
            'values' => ['a' => 'A', 'b' => 'B']
        ]);

        $this->assertEquals(['a' => 'A', 'b' => 'B'], $form->data());
    }

    public function testUntranslatedFields()
    {
        $app = new App([
            'roots' => [
                'index' => '/dev/null'
            ],
            'options' => [
                'languages' => true
            ],
            'languages' => [
                [
                    'code'    => 'en',
                    'default' => true
                ],
                [
                    'code' => 'de'
                ]
            ]
        ]);

        $page = new Page([
            'slug' => 'test',
            'blueprint' => [
                'fields' => [
                    'a' => [
                        'type' => 'text'
                    ],
                    'b' => [
                        'type' => 'text',
                        'translate' => false
                    ]
                ],
            ]
        ]);

        // default language
        $form = Form::for($page, [
            'input' => [
                'a' => 'A',
                'b' => 'B'
            ]
        ]);

        $expected = [
            'a' => 'A',
            'b' => 'B'
        ];

        $this->assertEquals($expected, $form->values());

        // secondary language
        $form = Form::for($page, [
            'language' => 'de',
            'input' => [
                'a' => 'A',
                'b' => 'B'
            ]
        ]);

        $expected = [
            'a' => 'A',
            'b' => ''
        ];

        $this->assertEquals($expected, $form->values());
    }
}
