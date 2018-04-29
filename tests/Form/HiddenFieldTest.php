<?php

namespace Kirby\Form;

class HiddenFieldTest extends FieldTestCase
{

    static protected $type = 'hidden';

    public function testValue()
    {
        $this->assertPropertyValue('value', 'test');
    }
}
