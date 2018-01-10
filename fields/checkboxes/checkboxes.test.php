<?php

namespace Kirby\Cms\FieldTest;

use Kirby\Cms\FieldTestCase;

class CheckboxesFieldTest extends FieldTestCase
{

    public function type(): string
    {
        return 'checkboxes';
    }

    public function props(): array
    {
        return [
            'name' => 'test',
            'options' => $this->options()
        ];
    }

    public function options(): array
    {
        return [
            ['value' => 'a', 'text' => 'A'],
            ['value' => 'b', 'text' => 'B'],
            ['value' => 'c', 'text' => 'C'],
        ];
    }

    public function testOptions()
    {
        $field = $this->field();
        $this->assertEquals($this->options(), $field->options());
    }

    public function testValues()
    {
        $field = $this->field();
        $this->assertEquals(['a', 'b', 'c'], $field->values());
    }

}
