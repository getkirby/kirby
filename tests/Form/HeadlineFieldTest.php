<?php

namespace Kirby\Form;

class HeadlineFieldTest extends FieldTestCase
{

    static protected $type = 'headline';

    public function testNumbered()
    {
        $this->assertPropertyDefault('numbered', true);
        $this->assertPropertyIsBool('numbered');
    }
}
