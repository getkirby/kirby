<?php

namespace Kirby\Cms\FieldTest;

use Kirby\Cms\FieldTestCase;

class HiddenFieldTest extends FieldTestCase
{

    public function type(): string
    {
        return 'hidden';
    }

    public function props(): array
    {
        return [
            'name' => 'test'
        ];
    }

    public function testToArray()
    {
        $expected = [
            'name'  => 'test',
            'type'  => 'hidden',
            'value' => 'test'
        ];

        $this->assertEquals($expected, $this->field(['value' => 'test'])->toArray());
    }

}
