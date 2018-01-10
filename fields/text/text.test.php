<?php

namespace Kirby\Cms\FieldTest;

use Kirby\Cms\FieldTestCase;

class TextFieldTest extends FieldTestCase
{

    public function type(): string
    {
        return 'text';
    }

    public function props(): array
    {
        return [
            'name' => 'test'
        ];
    }

    public function testDefaultAutocomplete()
    {
        $this->assertNull($this->field()->autocomplete());
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
