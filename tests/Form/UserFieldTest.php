<?php

namespace Kirby\Form;

class UserFieldTest extends FieldTestCase
{

    public function className(): string
    {
        return UserField::class;
    }

    public function testAutofocus()
    {
        $this->assertAutofocusProperty();
    }

    public function testHelp()
    {
        $this->assertHelpProperty();
    }

    public function testIcon()
    {
        $this->assertIconProperty('user');
    }

    public function testLabel()
    {
        $this->assertLabelProperty('User');
    }

    public function testName()
    {
        $this->assertNameProperty('user');
    }

    public function testRequired()
    {
        $this->assertRequiredProperty();
    }

    public function testType()
    {
        $this->assertTypeProperty('user');
    }

}
