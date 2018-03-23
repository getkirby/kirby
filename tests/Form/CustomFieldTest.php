<?php

namespace Kirby\Form;

use PHPUnit\Framework\TestCase as BaseTestCase;

class CustomTextField extends TextField
{

}

class CustomFieldTest extends BaseTestCase
{

    public function setUp()
    {
        Field::$types = [];
    }

    public function tearDown()
    {
        Field::$types = [];
    }

    public function testNew()
    {
        Field::$types['customText'] = CustomTextField::class;

        $field = Field::factory([
            'type' => 'customText',
            'name' => 'customText'
        ]);

        $this->assertInstanceOf(CustomTextField::class, $field);
    }

    public function testOverwrite()
    {
        Field::$types['text'] = CustomTextField::class;

        $field = Field::factory([
            'type' => 'text',
            'name' => 'text'
        ]);

        $this->assertInstanceOf(CustomTextField::class, $field);
    }

}
