<?php

namespace Kirby\Form;

class UserFieldTest extends FieldTestCase
{

    static protected $type = 'user';

    public function testIcon()
    {
        $this->assertIconProperty('user');
    }

    public function testRequired()
    {
        $this->assertRequiredProperty();
    }
}
