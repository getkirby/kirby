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

}
