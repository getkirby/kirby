<?php

namespace Kirby\Form;

class LineFieldTest extends FieldTestCase
{

    public function className(): string
    {
        return LineField::class;
    }

    public function testName()
    {
        $this->assertNameProperty('line');
    }

    public function testType()
    {
        $this->assertTypeProperty('line');
    }

}
