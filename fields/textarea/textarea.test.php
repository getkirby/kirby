<?php

namespace Kirby\Cms\FieldTest;

use Kirby\Cms\FieldTestCase;

class TextareaFieldTest extends FieldTestCase
{

    public function type(): string
    {
        return 'textarea';
    }

    public function testDefaultName()
    {
        $this->assertEquals('text', $this->field()->name());
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage The text is too long
     */
    public function testExceedMaxLength()
    {
        $field = $this->field([
            'maxLength' => 3
        ]);

        $field->submit('abcd');
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage The text is too short
     */
    public function testExceedMinLength()
    {
        $field = $this->field([
            'minLength' => 3
        ]);

        $field->submit('ab');
    }

}
