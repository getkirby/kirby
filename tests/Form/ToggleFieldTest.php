<?php

namespace Kirby\Form;

class ToggleFieldTest extends CheckboxFieldTest
{

    public function className(): string
    {
        return ToggleField::class;
    }

    public function testType()
    {
        $this->assertTypeProperty('toggle');
    }

}
